<?php

namespace App\Livewire;

use App\Models\FdComment;
use App\Models\FdCompany;
use App\Models\FdContact;
use App\Models\FdTicket;
use Illuminate\Support\Facades\Artisan;
use Livewire\Component;
use Livewire\WithPagination;

class FreshdeskImportDashboard extends Component
{
    use WithPagination;

    public string $log = '';
    public bool $running = false;
    public string $runningContactId = '';

    // Bulk selection
    public array $selected = [];
    public bool $selectAll = false;

    // Options for import-by-contact
    public bool $skipImported = false;
    public bool $withComments = false;
    public string $contactId = '';

    // Options for general import
    public string $importType = 'companies';
    public string $since = '';
    public string $until = '';

    // Table filter
    public string $search = '';
    public string $filterSynced = 'all'; // all | synced | pending
    public string $filterCompany = 'all'; // all | no_company | <fd_id>

    public function updatingSearch(): void
    {
        $this->resetPage();
        $this->selected = [];
        $this->selectAll = false;
    }

    public function updatingFilterSynced(): void
    {
        $this->resetPage();
        $this->selected = [];
        $this->selectAll = false;
    }

    public function updatingFilterCompany(): void
    {
        $this->resetPage();
        $this->selected = [];
        $this->selectAll = false;
    }

    public function updatedSelectAll(bool $value): void
    {
        if ($value) {
            $this->selected = FdContact::query()
                ->when($this->search, fn ($q) => $q->where(function ($q) {
                    $q->where('name', 'like', "%{$this->search}%")
                      ->orWhere('email', 'like', "%{$this->search}%");
                }))
                ->when($this->filterSynced === 'synced',  fn ($q) => $q->whereNotNull('tickets_imported_at'))
                ->when($this->filterSynced === 'pending', fn ($q) => $q->whereNull('tickets_imported_at'))
                ->when($this->filterCompany === 'no_company', fn ($q) => $q->whereNull('fd_company_id'))
                ->when($this->filterCompany !== 'all' && $this->filterCompany !== 'no_company',
                    fn ($q) => $q->where('fd_company_id', $this->filterCompany))
                ->orderByRaw('tickets_imported_at IS NOT NULL ASC, fd_id ASC')
                ->limit(100)
                ->pluck('fd_id')
                ->map(fn ($id) => (string) $id)
                ->toArray();
        } else {
            $this->selected = [];
        }
    }

    public function importBulk(): void
    {
        if (empty($this->selected)) {
            return;
        }

        set_time_limit(0);

        $this->running = true;
        $this->log     = '';
        $logs          = [];

        foreach ($this->selected as $fdId) {
            $args = ['--contact' => $fdId, '--no-interaction' => true];
            if ($this->withComments) $args['--with-comments'] = true;

            Artisan::call('freshdesk:import-by-contact', $args);
            $logs[] = "Contact {$fdId}: ".trim(Artisan::output());
        }

        $this->log     = implode("\n", $logs);
        $this->selected  = [];
        $this->selectAll = false;
        $this->running   = false;
    }

    public function stats(): array
    {
        return [
            'companies'        => FdCompany::count(),
            'companies_synced' => FdCompany::whereNotNull('tickets_imported_at')->count(),
            'contacts'         => FdContact::count(),
            'contacts_synced'  => FdContact::whereNotNull('tickets_imported_at')->count(),
            'tickets'          => FdTicket::count(),
            'comments'         => FdComment::count(),
        ];
    }

    public function runImport(): void
    {
        set_time_limit(0);
        $this->running = true;
        $this->log     = '';

        $args = ['--type' => $this->importType, '--no-interaction' => true];
        if ($this->since) $args['--since'] = $this->since;
        if ($this->until) $args['--until'] = $this->until;

        Artisan::call('freshdesk:import', $args);
        $this->log = Artisan::output();

        $this->running = false;
    }

    public function runImportByContact(): void
    {
        set_time_limit(0);
        $this->running = true;
        $this->log     = '';

        $args = ['--no-interaction' => true];
        if ($this->skipImported) $args['--skip-imported'] = true;
        if ($this->withComments)  $args['--with-comments'] = true;
        if ($this->contactId)     $args['--contact'] = $this->contactId;

        Artisan::call('freshdesk:import-by-contact', $args);
        $this->log = Artisan::output();

        $this->running = false;
    }

    public function importContact(int $fdId): void
    {
        $this->runningContactId = (string) $fdId;
        $this->log              = '';

        $args = ['--contact' => $fdId, '--no-interaction' => true];
        if ($this->withComments) $args['--with-comments'] = true;

        Artisan::call('freshdesk:import-by-contact', $args);
        $this->log = Artisan::output();

        $this->runningContactId = '';
    }

    public function render(): \Illuminate\View\View
    {
        $contacts = FdContact::query()
            ->when($this->search, fn ($q) => $q->where(function ($q) {
                $q->where('name', 'like', "%{$this->search}%")
                  ->orWhere('email', 'like', "%{$this->search}%");
            }))
            ->when($this->filterSynced === 'synced',  fn ($q) => $q->whereNotNull('tickets_imported_at'))
            ->when($this->filterSynced === 'pending', fn ($q) => $q->whereNull('tickets_imported_at'))
            ->when($this->filterCompany === 'no_company', fn ($q) => $q->whereNull('fd_company_id'))
            ->when($this->filterCompany !== 'all' && $this->filterCompany !== 'no_company',
                fn ($q) => $q->where('fd_company_id', $this->filterCompany))
            ->orderByRaw('tickets_imported_at IS NOT NULL ASC, fd_id ASC')
            ->paginate(20);

        $companies = FdCompany::orderBy('name')->get(['fd_id', 'name']);

        return view('livewire.freshdesk-import-dashboard', [
            'stats'     => $this->stats(),
            'contacts'  => $contacts,
            'companies' => $companies,
        ]);
    }
}
