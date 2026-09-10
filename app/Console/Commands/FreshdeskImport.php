<?php

namespace App\Console\Commands;

use App\Models\FdComment;
use App\Models\FdCompany;
use App\Models\FdContact;
use App\Models\FdTicket;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Http;

class FreshdeskImport extends Command
{
    protected $signature = 'freshdesk:import
                            {--type=all : What to import: all, companies, contacts, tickets, comments}
                            {--since= : Import tickets updated since this date (Y-m-d), e.g. 2024-01-01}
                            {--until= : Stop importing tickets updated after this date (Y-m-d), e.g. 2024-12-31}
                            {--company= : Only import tickets for this FD company ID}
                            {--fresh : Skip already-imported records (default: upsert)}';

    protected $description = 'Import all data from Freshdesk into local archive';

    private string $baseUrl;
    private string $auth;

    public function handle(): int
    {
        $domain = config('services.freshdesk.domain');
        $apiKey = config('services.freshdesk.api_key');

        if (! $domain || ! $apiKey) {
            $this->error('Set FRESHDESK_DOMAIN and FRESHDESK_API_KEY in .env');
            return self::FAILURE;
        }

        $this->baseUrl = str_starts_with($domain, 'http')
            ? rtrim($domain, '/')
            : "https://{$domain}.freshdesk.com";

        $this->auth = 'Basic '.base64_encode("{$apiKey}:X");

        $type = $this->option('type');

        if (in_array($type, ['all', 'companies'])) {
            $this->importCompanies();
        }

        if (in_array($type, ['all', 'contacts'])) {
            $this->importContacts();
        }

        if (in_array($type, ['all', 'tickets'])) {
            $this->importTickets();
        }

        if (in_array($type, ['all', 'comments'])) {
            $this->importComments();
        }

        $this->info('✅ Import complete.');
        return self::SUCCESS;
    }

    // ── Companies ──────────────────────────────────────────────────────────────

    private function importCompanies(): void
    {
        $this->info('Importing companies...');
        $page = 1;
        $total = 0;

        do {
            $rows = $this->get("/api/v2/companies?per_page=100&page={$page}");
            if (empty($rows)) break;

            foreach ($rows as $c) {
                FdCompany::updateOrCreate(
                    ['fd_id' => $c['id']],
                    [
                        'name'          => $c['name'],
                        'domains'       => isset($c['domains']) ? implode(',', $c['domains']) : null,
                        'description'   => $c['description'] ?? null,
                        'note'          => $c['note'] ?? null,
                        'custom_fields' => $c['custom_fields'] ?? null,
                        'fd_created_at' => $c['created_at'] ?? null,
                        'fd_updated_at' => $c['updated_at'] ?? null,
                    ]
                );
                $total++;
            }

            $this->line("  Companies page {$page}: ".count($rows)." rows");
            $page++;
        } while (count($rows) === 100);

        $this->info("  → {$total} companies imported.");
    }

    // ── Contacts ───────────────────────────────────────────────────────────────

    private function importContacts(): void
    {
        $this->info('Importing contacts...');
        $page = 1;
        $total = 0;

        do {
            $rows = $this->get("/api/v2/contacts?per_page=100&page={$page}");
            if (empty($rows)) break;

            foreach ($rows as $c) {
                FdContact::updateOrCreate(
                    ['fd_id' => $c['id']],
                    [
                        'fd_company_id' => $c['company_id'] ?? null,
                        'name'          => $c['name'] ?? '(no name)',
                        'email'         => $c['email'] ?? null,
                        'phone'         => $c['phone'] ?? null,
                        'mobile'        => $c['mobile'] ?? null,
                        'job_title'     => $c['job_title'] ?? null,
                        'language'      => $c['language'] ?? null,
                        'time_zone'     => $c['time_zone'] ?? null,
                        'custom_fields' => $c['custom_fields'] ?? null,
                        'fd_created_at' => $c['created_at'] ?? null,
                        'fd_updated_at' => $c['updated_at'] ?? null,
                    ]
                );
                $total++;
            }

            $this->line("  Contacts page {$page}: ".count($rows)." rows");
            $page++;
        } while (count($rows) === 100);

        $this->info("  → {$total} contacts imported.");
    }

    // ── Tickets ────────────────────────────────────────────────────────────────

    private function importTickets(): void
    {
        $this->info('Importing tickets...');
        $page = 1;
        $total = 0;

        $params = 'per_page=100&include=description';
        if ($since = $this->option('since')) {
            $params .= '&updated_since='.urlencode($since.'T00:00:00Z');
        }
        if ($companyId = $this->option('company')) {
            $params .= '&company_id='.$companyId;
        }

        $until = $this->option('until') ? new \DateTime($this->option('until').' 23:59:59') : null;

        do {
            $rows = $this->get("/api/v2/tickets?{$params}&page={$page}");
            if (empty($rows)) break;

            // Filter client-side by until date (FD API has no updated_before param)
            if ($until) {
                $rows = array_filter($rows, fn($t) => new \DateTime($t['updated_at']) <= $until);
            }

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
                $total++;
            }

            $this->line("  Tickets page {$page}: ".count($rows)." rows");
            $page++;
        } while (count($rows) === 100);

        $this->info("  → {$total} tickets imported.");
    }

    // ── Comments ───────────────────────────────────────────────────────────────

    private function importComments(): void
    {
        $this->info('Importing comments (conversations)...');

        $ticketIds = FdTicket::pluck('fd_id');
        $total = 0;
        $bar = $this->output->createProgressBar($ticketIds->count());
        $bar->start();

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
                            'attachments'   => collect($conv['attachments'] ?? [])->map(fn($a) => [
                                'name' => $a['name'],
                                'url'  => $a['attachment_url'] ?? $a['url'] ?? null,
                                'size' => $a['size'] ?? null,
                            ])->toArray(),
                            'fd_created_at' => $conv['created_at'] ?? null,
                            'fd_updated_at' => $conv['updated_at'] ?? null,
                        ]
                    );
                    $total++;
                }

                $page++;
            } while (count($rows) === 100);

            $bar->advance();
        }

        $bar->finish();
        $this->newLine();
        $this->info("  → {$total} comments imported.");
    }

    // ── HTTP helper ────────────────────────────────────────────────────────────

    private function get(string $path): array
    {
        $url = $this->baseUrl.$path;

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
                $this->error("HTTP {$response->status()} for {$url}");
                throw new \Exception("HTTP {$response->status()}");
            }
        }, 2000);

        return $response->json() ?? [];
    }
}
