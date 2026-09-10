<?php

namespace App\Console\Commands;

use App\Models\FdComment;
use App\Models\FdTicket;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Http;

class FreshdeskSyncRecent extends Command
{
    protected $signature = 'freshdesk:sync-recent
                            {--days=7 : Berapa hari kebelakang (default: 7)}
                            {--with-comments : Import comments sekalian}
                            {--no-comments : Skip comments (default kalau tidak pakai --with-comments)}';

    protected $description = 'Sync tickets yang diupdate dalam N hari terakhir beserta comments-nya';

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

        $days        = max(1, (int) $this->option('days'));
        $since       = now()->utc()->subDays($days)->format('Y-m-d\TH:i:s\Z');
        $withComments = $this->option('with-comments');

        $this->info("Syncing tickets updated since: {$since} ({$days} hari terakhir)");

        $page          = 1;
        $totalTickets  = 0;
        $totalComments = 0;
        $errors        = 0;

        do {
            $this->line("Fetching page {$page}...");
            $rows = $this->get("/api/v2/tickets?updated_since={$since}&per_page=100&include=description&page={$page}");

            if (empty($rows)) break;

            foreach ($rows as $t) {
                try {
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

                    $this->line("  ticket #{$t['id']} — {$t['subject']}");
                    $totalTickets++;

                    if ($withComments) {
                        $ticket = FdTicket::find($t['id']);
                        if (! $ticket?->comments_imported_at) {
                            $count = $this->importComments($t['id']);
                            $totalComments += $count;
                            $ticket?->update(['comments_imported_at' => now()]);
                        } else {
                            $this->line("  skip comments (sudah diimport)");
                        }
                    }
                } catch (\Throwable $e) {
                    $errors++;
                    $this->warn("  ⚠ ticket #{$t['id']}: {$e->getMessage()}");
                }
            }

            $page++;
        } while (count($rows) === 100);

        $this->newLine();
        $this->info("✅ Selesai — {$totalTickets} tickets, {$totalComments} comments, {$errors} error.");

        return self::SUCCESS;
    }

    private function importComments(int $ticketId): int
    {
        $page  = 1;
        $count = 0;

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

            if (! $response->successful()) {
                throw new \Exception("HTTP {$response->status()}");
            }
        }, 2000);

        return $response->json() ?? [];
    }
}
