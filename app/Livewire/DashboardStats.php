<?php

namespace App\Livewire;

use App\Models\FdComment;
use App\Models\FdCompany;
use App\Models\FdContact;
use App\Models\FdTicket;
use Illuminate\Support\Facades\Artisan;
use Livewire\Component;

class DashboardStats extends Component
{
    public bool   $syncing  = false;
    public string $syncLog  = '';

    public function quickSync(): void
    {
        set_time_limit(0);
        $this->syncing = true;
        $this->syncLog = '';

        // last sync time across tickets — minus 1 day as safety buffer
        $lastSync = FdTicket::max('updated_at');
        $since = $lastSync
            ? \Carbon\Carbon::parse($lastSync)->subDay()->format('Y-m-d')
            : now()->subDays(7)->format('Y-m-d');

        $logs = [];
        foreach (['tickets', 'contacts', 'companies'] as $type) {
            Artisan::call('freshdesk:import', [
                '--type'           => $type,
                '--since'          => $since,
                '--no-interaction' => true,
            ]);
            $logs[] = strtoupper($type).': '.trim(Artisan::output());
        }

        $this->syncLog  = implode("\n", $logs);
        $this->syncing  = false;
    }

    public function render()
    {
        $tickets   = FdTicket::count();
        $contacts  = FdContact::count();
        $companies = FdCompany::count();
        $comments  = FdComment::count();

        $contactsSynced  = FdContact::whereNotNull('tickets_imported_at')->count();
        $companiesSynced = FdCompany::whereNotNull('tickets_imported_at')->count();

        $lastTicketSync  = FdTicket::max('updated_at');
        $lastContactSync = FdContact::max('last_synced_at') ?? FdContact::max('updated_at');
        $lastCompanySync = FdCompany::max('last_synced_at') ?? FdCompany::max('updated_at');

        // compute the since date that quickSync would use (for display)
        $sincePrev = $lastTicketSync
            ? \Carbon\Carbon::parse($lastTicketSync)->subDay()->format('Y-m-d')
            : now()->subDays(7)->format('Y-m-d');

        return view('livewire.dashboard-stats', compact(
            'tickets', 'contacts', 'companies', 'comments',
            'contactsSynced', 'companiesSynced',
            'lastTicketSync', 'lastContactSync', 'lastCompanySync',
            'sincePrev'
        ));
    }
}
