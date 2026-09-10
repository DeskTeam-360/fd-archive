<?php

namespace App\Console\Commands;

use App\Models\FdTicket;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Http;

class FreshdeskImportMissing extends Command
{
    protected $signature = 'freshdesk:import-missing
                            {--with-comments : Import comments untuk ticket baru}
                            {--dry-run : Hanya tampilkan berapa yang missing, tidak import}';

    protected $description = 'Cari dan import ticket yang belum ada di DB dengan cepat';

    private string $baseUrl;
    private string $auth;

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

        // Step 1: Kumpulkan semua FD ticket ID (tanpa description — cepat)
        $this->info('Step 1: Mengumpulkan semua ticket ID dari Freshdesk...');
        $fdIds = $this->fetchAllIds();
        $this->line("  Total di Freshdesk: " . count($fdIds));

        // Step 2: Bandingkan dengan DB
        $this->info('Step 2: Membandingkan dengan database...');
        $existingIds = FdTicket::whereIn('fd_id', $fdIds)->pluck('fd_id')->flip();
        $missingIds  = array_values(array_filter($fdIds, fn ($id) => ! $existingIds->has($id)));
        $this->line("  Sudah ada di DB : " . $existingIds->count());
        $this->line("  Missing         : " . count($missingIds));

        if (empty($missingIds)) {
            $this->info('✅ Tidak ada ticket yang missing!');
            return self::SUCCESS;
        }

        if ($this->option('dry-run')) {
            $this->warn('Dry run — tidak ada yang diimport. Jalankan tanpa --dry-run untuk import.');
            return self::SUCCESS;
        }

        // Step 3: Fetch dan import yang missing saja
        $this->info('Step 3: Importing ' . count($missingIds) . ' ticket missing...');
        $bar = $this->output->createProgressBar(count($missingIds));
        $bar->start();

        $totalTickets  = 0;
        $totalComments = 0;
        $errors        = 0;

        // Proses per batch untuk efisiensi
        foreach (array_chunk($missingIds, 1) as $chunk) {
            foreach ($chunk as $ticketId) {
                try {
                    $t = $this->get("/api/v2/tickets/{$ticketId}?include=description");

                    if (empty($t)) {
                        $bar->advance();
                        continue;
                    }

                    FdTicket::updateOrCreate(
                        ['fd_id' => $t['id']],
                        [
                            'fd_company_id'    => $t['company_id'] ?? null,
                            'fd_requester_id'  => $t['requester_id'] ?? null,
                            'fd_responder_id'  => $t['responder_id'] ?? null,
                            'subject'          => $t['subject'] ?? '(no subject)',
                            'description'      => $t['description'] ?? null,
                            'description_text' => $t['description_text'] ?? null,
                            'status'           => $t['status'],
                            'status_label'     => FdTicket::STATUS_MAP[$t['status']] ?? null,
                            'priority'         => $t['priority'],
                            'priority_label'   => FdTicket::PRIORITY_MAP[$t['priority']] ?? null,
                            'type'             => $t['type'] ?? null,
                            'source'           => $t['source'] ?? null,
                            'tags'             => $t['tags'] ?? [],
                            'custom_fields'    => $t['custom_fields'] ?? null,
                            'due_by'           => $t['due_by'] ?? null,
                            'fr_due_by'        => $t['fr_due_by'] ?? null,
                            'fd_created_at'    => $t['created_at'],
                            'fd_updated_at'    => $t['updated_at'],
                        ]
                    );

                    $totalTickets++;

                    if ($this->option('with-comments')) {
                        $totalComments += $this->importComments($ticketId);
                        FdTicket::where('fd_id', $ticketId)->update(['comments_imported_at' => now()]);
                    }
                } catch (\Throwable $e) {
                    $errors++;
                    $bar->clear();
                    $this->warn("  ⚠ ticket #{$ticketId}: {$e->getMessage()}");
                }

                $bar->advance();
            }
        }

        $bar->finish();
        $this->newLine(2);
        $this->info("✅ Selesai — {$totalTickets} tickets, {$totalComments} comments, {$errors} error.");

        return self::SUCCESS;
    }

    private function fetchAllIds(): array
    {
        $ids   = [];
        $years = range(2018, (int) now()->format('Y'));

        // FD Search API support filter created_at, max 30 pages x 30 results = 900 per query
        // Split per bulan untuk cover semua ticket
        $currentYear  = (int) now()->format('Y');
        $currentMonth = (int) now()->format('m');

        foreach ($years as $year) {
            $months = ($year === $currentYear) ? range(1, $currentMonth) : range(1, 12);

            foreach ($months as $month) {
                $from  = sprintf('%d-%02d-01', $year, $month);
                $lastDay = cal_days_in_month(CAL_GREGORIAN, $month, $year);
                $to    = sprintf('%d-%02d-%02d', $year, $month, $lastDay);

                $page  = 1;
                $count = 0;

                do {
                    $query = "\"created_at:>'{$from}' AND created_at:<'{$to}'\"";
                    try {
                        $rows  = $this->get("/api/v2/search/tickets?query=" . urlencode($query) . "&page={$page}");
                    } catch (\Exception $e) {
                        break; // 400 = no more pages
                    }
                    $items = $rows['results'] ?? [];
                    if (empty($items)) break;

                    foreach ($items as $t) {
                        $ids[$t['id']] = true;
                    }

                    $count += count($items);
                    $page++;
                } while (count($items) === 30 && $page <= 30);

                if ($count > 0) {
                    $this->line("  [{$year}-{$month}] {$count} tickets");
                }
            }
        }

        return array_keys($ids);
    }

    private function importComments(int $ticketId): int
    {
        $page  = 1;
        $count = 0;

        do {
            $rows = $this->get("/api/v2/tickets/{$ticketId}/conversations?per_page=100&page={$page}");
            if (empty($rows)) break;

            foreach ($rows as $conv) {
                \App\Models\FdComment::updateOrCreate(
                    ['fd_id' => $conv['id']],
                    [
                        'fd_ticket_id'  => $ticketId,
                        'fd_user_id'    => $conv['user_id'] ?? null,
                        'body'          => $conv['body'] ?? null,
                        'body_text'     => $conv['body_text'] ?? null,
                        'incoming'      => $conv['incoming'] ?? false,
                        'private'       => $conv['private'] ?? false,
                        'from_email'    => $conv['from_email'] ?? null,
                        'to_emails'     => $conv['to_emails'] ?? [],
                        'cc_emails'     => $conv['cc_emails'] ?? [],
                        'bcc_emails'    => $conv['bcc_emails'] ?? [],
                        'attachments'   => collect($conv['attachments'] ?? [])->map(fn ($a) => [
                            'name' => $a['name'],
                            'url'  => $a['attachment_url'] ?? $a['url'] ?? null,
                            'size' => $a['size'] ?? null,
                        ])->toArray(),
                        'fd_created_at' => $conv['created_at'] ?? null,
                        'fd_updated_at' => $conv['updated_at'] ?? null,
                    ]
                );
                $count++;
            }

            $page++;
        } while (count($rows) === 100);

        return $count;
    }

    private function get(string $path): array
    {
        $url      = $this->baseUrl . $path;
        $response = null;

        retry(3, function () use ($url, &$response) {
            $response = Http::withHeaders(['Authorization' => $this->auth])
                ->timeout(30)
                ->get($url);

            if ($response->status() === 429) {
                $this->warn('Rate limited — waiting 60s...');
                sleep(60);
                throw new \Exception('Rate limited');
            }

            if ($response->status() === 400) {
                throw new \RuntimeException("HTTP 400"); // no retry
            }

            if (! $response->successful()) {
                throw new \Exception("HTTP {$response->status()}");
            }
        }, 2000, fn ($e) => ! ($e instanceof \RuntimeException));

        return $response->json() ?? [];
    }
}
