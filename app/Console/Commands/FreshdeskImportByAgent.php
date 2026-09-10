<?php

namespace App\Console\Commands;

use App\Models\FdAgent;
use App\Models\FdComment;
use App\Models\FdTicket;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Http;

class FreshdeskImportByAgent extends Command
{
    protected $signature = 'freshdesk:import-by-agent
                            {--skip-imported : Skip agents yang sudah pernah diimport (tickets_imported_at tidak null)}
                            {--agent= : Hanya import untuk satu fd_id agent tertentu}
                            {--limit=0 : Batasi jumlah agents yang diproses (0 = semua)}
                            {--with-comments : Setelah import tickets, langsung import comments-nya juga}';

    protected $description = 'Import tickets per agent (requester_id), update tickets_imported_at & last_synced_at on fd_agents';

    private string $baseUrl;
    private string $auth;

    public function handle(): int
    {
        set_time_limit(0);
        ini_set('memory_limit', '512M');

        $domain = config('services.freshdesk.domain');
        $apiKey = config('services.freshdesk.api_key');

        if (! $domain || ! $apiKey) {
            $this->error('Set FRESHDESK_DOMAIN dan FRESHDESK_API_KEY di .env');
            return self::FAILURE;
        }

        $this->baseUrl = str_starts_with($domain, 'http')
            ? rtrim($domain, '/')
            : "https://{$domain}.freshdesk.com";

        $this->auth = 'Basic '.base64_encode("{$apiKey}:X");

        $query = FdAgent::query();

        if ($agentId = $this->option('agent')) {
            $query->where('fd_id', $agentId);
        }

        if ($this->option('skip-imported')) {
            $query->whereNull('tickets_imported_at');
        }

        $limit = (int) $this->option('limit');
        if ($limit > 0) {
            $query->limit($limit);
        }

        $agents = $query->orderBy('fd_id')->get();

        if ($agents->isEmpty()) {
            $this->info('Tidak ada agent yang perlu diimport.');
            return self::SUCCESS;
        }

        $this->info("Total agents: {$agents->count()}");
        $bar = $this->output->createProgressBar($agents->count());
        $bar->start();

        $totalTickets  = 0;
        $totalComments = 0;
        $errors        = 0;

        foreach ($agents as $agent) {
            try {
                $ticketCount = $this->importTicketsForRequester($agent->fd_id);
                $totalTickets += $ticketCount;

                if ($this->option('with-comments') && $ticketCount > 0) {
                    $totalComments += $this->importCommentsForRequester($agent->fd_id);
                }

                $agent->update([
                    'tickets_imported_at' => $agent->tickets_imported_at ?? now(),
                    'last_synced_at'      => now(),
                ]);
            } catch (\Throwable $e) {
                $errors++;
                $this->newLine();
                $this->warn("  ⚠ Agent {$agent->fd_id} ({$agent->email}): {$e->getMessage()}");
            }

            $bar->advance();
        }

        $bar->finish();
        $this->newLine(2);
        $this->info("✅ Selesai — {$totalTickets} tickets, {$totalComments} comments, {$errors} error.");

        return self::SUCCESS;
    }

    private function importTicketsForRequester(int $requesterId): int
    {
        $page  = 1;
        $count = 0;

        do {
            $rows = $this->get("/api/v2/tickets?requester_id={$requesterId}&per_page=100&include=description&page={$page}");
            if (empty($rows)) break;

            foreach ($rows as $t) {
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
                $count++;
            }

            $page++;
        } while (count($rows) === 100);

        return $count;
    }

    private function importCommentsForRequester(int $requesterId): int
    {
        $ticketIds = FdTicket::where('fd_requester_id', $requesterId)->pluck('fd_id');
        $count     = 0;

        foreach ($ticketIds as $ticketId) {
            $page = 1;
            do {
                $rows = $this->get("/api/v2/tickets/{$ticketId}/conversations?per_page=100&page={$page}");
                if (empty($rows)) break;

                foreach ($rows as $conv) {
                    FdComment::updateOrCreate(
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
        }

        return $count;
    }

    private function get(string $path): array
    {
        $url      = $this->baseUrl.$path;
        $response = null;

        retry(3, function () use ($url, &$response) {
            $response = Http::withHeaders(['Authorization' => $this->auth])
                ->timeout(30)
                ->get($url);

            if ($response->status() === 429) {
                $this->newLine();
                $this->warn('Rate limited — waiting 60s...');
                sleep(60);
                throw new \Exception('Rate limited');
            }

            if (! $response->successful()) {
                throw new \Exception("HTTP {$response->status()}");
            }
        }, 2000);

        return $response->json() ?? [];
    }
}
