<?php

namespace App\Console\Commands;

use App\Models\FdAgent;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Http;

class FreshdeskImportAgents extends Command
{
    protected $signature = 'freshdesk:import-agents';
    protected $description = 'Import all agents from Freshdesk';

    public function handle(): int
    {
        $domain = config('services.freshdesk.domain');
        $apiKey = config('services.freshdesk.api_key');

        if (! $domain || ! $apiKey) {
            $this->error('Set FRESHDESK_DOMAIN dan FRESHDESK_API_KEY di .env');
            return self::FAILURE;
        }

        $baseUrl = str_starts_with($domain, 'http')
            ? rtrim($domain, '/')
            : "https://{$domain}.freshdesk.com";

        $auth = 'Basic '.base64_encode("{$apiKey}:X");

        $page  = 1;
        $total = 0;

        $this->info('Importing agents...');

        do {
            $response = Http::withHeaders(['Authorization' => $auth])
                ->timeout(30)
                ->get("{$baseUrl}/api/v2/agents?per_page=100&page={$page}");

            if ($response->status() === 429) {
                $this->warn('Rate limited — waiting 60s...');
                sleep(60);
                continue;
            }

            if (! $response->successful()) {
                $this->error("HTTP {$response->status()}");
                return self::FAILURE;
            }

            $agents = $response->json() ?? [];
            if (empty($agents)) break;

            foreach ($agents as $a) {
                $contact = $a['contact'] ?? [];
                FdAgent::updateOrCreate(
                    ['fd_id' => $a['id']],
                    [
                        'name'          => $contact['name'] ?? $a['name'] ?? 'Unknown',
                        'email'         => $contact['email'] ?? $a['email'] ?? null,
                        'phone'         => $contact['phone'] ?? null,
                        'mobile'        => $contact['mobile'] ?? null,
                        'job_title'     => $contact['job_title'] ?? null,
                        'occasional'    => $a['occasional'] ?? false,
                        'active'        => $a['active'] ?? true,
                        'type'          => $a['type'] ?? null,
                        'group_ids'     => $a['group_ids'] ?? [],
                        'role_ids'      => $a['role_ids'] ?? [],
                        'skill_ids'     => $a['skill_ids'] ?? [],
                        'custom_fields' => $contact['custom_fields'] ?? null,
                        'fd_created_at' => $a['created_at'] ?? null,
                        'fd_updated_at' => $a['updated_at'] ?? null,
                    ]
                );
                $total++;
            }

            $this->line("  Page {$page}: ".count($agents)." agents");
            $page++;
        } while (count($agents) === 100);

        $this->info("✅ Done — {$total} agents imported.");
        return self::SUCCESS;
    }
}
