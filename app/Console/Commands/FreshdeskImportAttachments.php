<?php

namespace App\Console\Commands;

use App\Models\FdAttachment;
use App\Models\FdTicket;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Str;

class FreshdeskImportAttachments extends Command
{
    protected $signature = 'freshdesk:import-attachments
                            {--from=1 : Ticket ID awal}
                            {--to=99999999 : Ticket ID akhir}
                            {--ticket=* : Ticket ID spesifik (bisa berulang)}
                            {--force : Proses ulang ticket yang sudah attachments_imported_at}
                            {--retry-failed : Hanya ulangi attachment yang sebelumnya gagal}';

    protected $description = 'Download attachment ticket & conversation dari Freshdesk ke storage internal';

    private const DISK = 'local';

    private string $baseUrl;
    private string $auth;

    private int $downloaded = 0;
    private int $skipped = 0;
    private int $failed = 0;

    public function handle(): int
    {
        $domain = config('services.freshdesk.domain');
        $apiKey = config('services.freshdesk.api_key');

        if (! $domain || ! $apiKey) {
            $this->error('Set FRESHDESK_DOMAIN dan FRESHDESK_API_KEY di .env');
            return self::FAILURE;
        }

        set_time_limit(0);
        ini_set('memory_limit', '512M');

        $this->baseUrl = str_starts_with($domain, 'http')
            ? rtrim($domain, '/')
            : "https://{$domain}.freshdesk.com";
        $this->auth = 'Basic ' . base64_encode("{$apiKey}:X");

        $query = FdTicket::query()->orderBy('fd_id');

        if ($ids = $this->option('ticket')) {
            $query->whereIn('fd_id', array_map('intval', $ids));
        } elseif ($this->option('retry-failed')) {
            $query->whereIn('fd_id', FdAttachment::whereNull('downloaded_at')->distinct()->pluck('fd_ticket_id'));
        } else {
            $query->whereBetween('fd_id', [(int) $this->option('from'), (int) $this->option('to')]);
            if (! $this->option('force')) {
                $query->whereNull('attachments_imported_at');
            }
        }

        $total = (clone $query)->count();
        $this->info("Memproses {$total} ticket...");
        if ($total === 0) {
            return self::SUCCESS;
        }

        $bar = $this->output->createProgressBar($total);
        $bar->start();

        $query->select('fd_id')->chunkById(200, function ($tickets) use ($bar) {
            foreach ($tickets as $ticket) {
                try {
                    $this->processTicket($ticket->fd_id);
                    FdTicket::where('fd_id', $ticket->fd_id)->update(['attachments_imported_at' => now()]);
                } catch (\Throwable $e) {
                    $bar->clear();
                    $this->warn("  ⚠ ticket #{$ticket->fd_id}: {$e->getMessage()}");
                    $bar->display();
                }
                $bar->advance();
            }
        }, 'fd_id');

        $bar->finish();
        $this->newLine(2);
        $this->info("✅ Selesai — downloaded: {$this->downloaded}, skipped: {$this->skipped}, failed: {$this->failed}");

        return self::SUCCESS;
    }

    private function processTicket(int $ticketId): void
    {
        // Attachment URL dari FD adalah signed URL yang cepat expire, jadi selalu fetch ulang dari API.
        $ticket = $this->get("/api/v2/tickets/{$ticketId}");
        foreach ($ticket['attachments'] ?? [] as $att) {
            $this->storeAttachment($att, $ticketId, null);
        }

        $page = 1;
        do {
            $rows = $this->get("/api/v2/tickets/{$ticketId}/conversations?per_page=100&page={$page}");
            foreach ($rows as $conv) {
                foreach ($conv['attachments'] ?? [] as $att) {
                    $this->storeAttachment($att, $ticketId, (int) $conv['id']);
                }
            }
            $page++;
        } while (count($rows) === 100);
    }

    private function storeAttachment(array $att, int $ticketId, ?int $commentId): void
    {
        $existing = FdAttachment::find($att['id']);
        if ($existing?->downloaded_at && is_file($this->absolutePath($existing->path))) {
            $this->skipped++;
            return;
        }

        $dir = $commentId
            ? "freshdesk/attachments/{$ticketId}/comments/{$commentId}"
            : "freshdesk/attachments/{$ticketId}";
        $path = $dir . '/' . $att['id'] . '_' . $this->safeName($att['name'] ?? 'file');

        $record = [
            'fd_ticket_id'  => $ticketId,
            'fd_comment_id' => $commentId,
            'name'          => $att['name'] ?? 'file',
            'content_type'  => $att['content_type'] ?? null,
            'size'          => $att['size'] ?? null,
            'disk'          => self::DISK,
            'path'          => $path,
        ];

        $url = $att['attachment_url'] ?? null;
        $error = $url ? $this->download($url, $path) : 'missing attachment_url';

        FdAttachment::updateOrCreate(['fd_id' => $att['id']], $record + [
            'downloaded_at' => $error ? null : now(),
            'error'         => $error,
        ]);

        if ($error) {
            $this->failed++;
            $this->warn("  ⚠ #{$ticketId} [{$record['name']}]: {$error}");
        } else {
            $this->downloaded++;
        }
    }

    /** Stream ke disk supaya file besar tidak memenuhi memory. Return null jika sukses, atau pesan error. */
    private function download(string $url, string $path): ?string
    {
        $tmp = tempnam(sys_get_temp_dir(), 'fdatt');
        $lastError = null;

        // Signed S3 URL biasanya menolak header Authorization, jadi coba tanpa auth dulu.
        foreach ([[], ['Authorization' => $this->auth]] as $headers) {
            try {
                $res = Http::withHeaders($headers)->timeout(120)->sink($tmp)->get($url);
                if ($res->successful()) {
                    // Plain filesystem only: building a Storage disk instantiates finfo, and the server CLI lacks ext-fileinfo.
                    $target = $this->absolutePath($path);
                    if (! is_dir(dirname($target))) {
                        mkdir(dirname($target), 0755, true);
                    }
                    if (! @rename($tmp, $target)) {
                        copy($tmp, $target);
                        @unlink($tmp);
                    }
                    return null;
                }
                $lastError = "HTTP {$res->status()}";
                if (! in_array($res->status(), [400, 401, 403], true)) {
                    break;
                }
            } catch (\Throwable $e) {
                $lastError = $e->getMessage();
                break;
            }
        }

        @unlink($tmp);
        return $lastError;
    }

    private function absolutePath(string $path): string
    {
        return storage_path('app/private/' . $path);
    }

    private function safeName(string $name): string
    {
        $ext = pathinfo($name, PATHINFO_EXTENSION);
        $base = Str::limit(Str::slug(pathinfo($name, PATHINFO_FILENAME), '_'), 150, '');
        return ($base ?: 'file') . ($ext ? '.' . Str::lower($ext) : '');
    }

    private function get(string $path): array
    {
        $url = $this->baseUrl . $path;
        $response = null;

        retry(3, function () use ($url, &$response) {
            $response = Http::withHeaders(['Authorization' => $this->auth])->timeout(30)->get($url);

            if ($response->status() === 429) {
                $wait = (int) ($response->header('Retry-After') ?: 60);
                $this->warn("Rate limited — waiting {$wait}s...");
                sleep($wait);
                throw new \Exception('Rate limited');
            }
            if (in_array($response->status(), [400, 404], true)) {
                throw new \RuntimeException("HTTP {$response->status()}");
            }
            if (! $response->successful()) {
                throw new \Exception("HTTP {$response->status()}");
            }
        }, 2000, fn ($e) => ! ($e instanceof \RuntimeException));

        return $response->json() ?? [];
    }
}
