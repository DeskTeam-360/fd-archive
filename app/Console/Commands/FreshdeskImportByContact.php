<?php

namespace App\Console\Commands;

use App\Models\FdComment;
use App\Models\FdContact;
use App\Models\FdTicket;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Http;

class FreshdeskImportByContact extends Command
{
    protected $signature = 'freshdesk:import-by-contact
                            {--skip-imported : Skip contacts yang sudah pernah diimport (tickets_imported_at tidak null)}
                            {--no-company : Hanya contacts yang tidak punya company (fd_company_id null)}
                            {--limit=0 : Batasi jumlah contacts yang diproses (0 = semua)}
                            {--contact= : Hanya import untuk satu fd_id contact tertentu}
                            {--with-comments : Setelah import tickets, langsung import comments-nya juga}';

    protected $description = 'Import tickets per contact (requester_id), update tickets_imported_at & last_synced_at';

    private string $baseUrl;
    private string $auth;

    public function handle(): int
    {
        $domain  = config('services.freshdesk.domain');
        $apiKey  = config('services.freshdesk.api_key');

        if (! $domain || ! $apiKey) {
            $this->error('Set FRESHDESK_DOMAIN dan FRESHDESK_API_KEY di .env');
            return self::FAILURE;
        }

        ini_set('memory_limit', '512M');

        $this->baseUrl = str_starts_with($domain, 'http')
            ? rtrim($domain, '/')
            : "https://{$domain}.freshdesk.com";

        $this->auth = 'Basic '.base64_encode("{$apiKey}:X");

        // Query contacts
        $query = FdContact::query();

        if ($contactId = $this->option('contact')) {
            $query->where('fd_id', $contactId);
        }

        if ($this->option('skip-imported')) {
            $query->whereNull('tickets_imported_at');
        }

        if ($this->option('no-company')) {
            $query->whereNull('fd_company_id');
        }

        $query->orderBy('fd_id');

        $limit = (int) $this->option('limit');
        if ($limit > 0) {
            $query->limit($limit);
        }

        $contacts = $query->get();

        if ($contacts->isEmpty()) {
            $this->info('Tidak ada contact yang perlu diimport.');
            return self::SUCCESS;
        }

        $this->info("Total contacts: {$contacts->count()}");
        $bar = $this->output->createProgressBar($contacts->count());
        $bar->start();

        $totalTickets  = 0;
        $totalComments = 0;
        $errors        = 0;

        foreach ($contacts as $contact) {
            $bar->clear();
            $this->line("→ Contact {$contact->fd_id} ({$contact->name} / {$contact->email})");

            try {
                [$ticketCount, $lastTicketId] = $this->importTicketsForContact($contact->fd_id);
                $totalTickets += $ticketCount;
                $this->line("  ✓ {$ticketCount} tickets" . ($lastTicketId ? ", last ticket #{$lastTicketId}" : ''));

                if ($this->option('with-comments') && $ticketCount > 0) {
                    $comments = $this->importCommentsForContact($contact->fd_id);
                    $totalComments += $comments;
                    $this->line("  ✓ {$comments} comments");
                }

                $contact->update([
                    'tickets_imported_at' => $contact->tickets_imported_at ?? now(),
                    'last_synced_at'      => now(),
                ]);
            } catch (\Throwable $e) {
                $errors++;
                $this->warn("  ⚠ Error: {$e->getMessage()}");
            }

            $bar->display();
            $bar->advance();
        }

        $bar->finish();
        $this->newLine(2);
        $this->info("✅ Selesai — {$totalTickets} tickets, {$totalComments} comments, {$errors} error.");

        return self::SUCCESS;
    }

    private function importTicketsForContact(int $contactId): array
    {
        $page       = 1;
        $count      = 0;
        $lastTicketId = null;

        do {
            $this->line("  fetching page {$page}...", null, 'v');
            $rows = $this->get("/api/v2/tickets?requester_id={$contactId}&per_page=100&include=description&page={$page}");
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
                $lastTicketId = $t['id'];
                $count++;
            }

            $page++;
        } while (count($rows) === 100);

        return [$count, $lastTicketId];
    }

    private function importCommentsForContact(int $contactId): int
    {
        $ticketIds = FdTicket::where('fd_requester_id', $contactId)->pluck('fd_id');
        $count     = 0;

        foreach ($ticketIds as $ticketId) {
            if (FdTicket::where('fd_id', $ticketId)->whereNotNull('comments_imported_at')->exists()) {
                continue;
            }
            $this->line("  comments ticket #{$ticketId}...");
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

            FdTicket::where('fd_id', $ticketId)->update(['comments_imported_at' => now()]);
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
