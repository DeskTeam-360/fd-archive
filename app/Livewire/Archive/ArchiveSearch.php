<?php

namespace App\Livewire\Archive;

use App\Models\FdAgent;
use App\Models\FdComment;
use App\Models\FdCompany;
use App\Models\FdContact;
use App\Models\FdTicket;
use Livewire\Component;
use Livewire\WithPagination;

class ArchiveSearch extends Component
{
    use WithPagination;

    public string $search = '';
    public string $tab = 'tickets';

    // Filters (tickets tab)
    public array  $filterAgents    = [];
    public array  $filterStatuses  = [];
    public array  $filterPriorities = [];
    public array  $filterTypes     = [];
    public array  $filterSources   = [];
    public array  $filterCompanies = [];
    public array  $filterContacts  = [];
    public array  $filterTags     = [];
    public string $filterCreatedFrom = '';
    public string $filterCreatedTo   = '';
    public bool   $showFilters = false;

    // Options (loaded once)
    protected array $agentOptions   = [];
    protected array $companyOptions = [];
    protected array $typeOptions    = [];

    public function updatingSearch(): void    { $this->resetPage(); }
    public function updatingTab(): void       { $this->resetPage(); $this->resetFilters(); }
    public function updatingFilterAgents(): void    { $this->resetPage(); }
    public function updatingFilterStatuses(): void  { $this->resetPage(); }
    public function updatingFilterPriorities(): void { $this->resetPage(); }
    public function updatingFilterTypes(): void     { $this->resetPage(); }
    public function updatingFilterSources(): void   { $this->resetPage(); }
    public function updatingFilterCompanies(): void { $this->resetPage(); }
    public function updatingFilterContacts(): void  { $this->resetPage(); }
    public function updatingFilterTags(): void      { $this->resetPage(); }
    public function updatingFilterCreatedFrom(): void { $this->resetPage(); }
    public function updatingFilterCreatedTo(): void   { $this->resetPage(); }

    public function mount(): void
    {
        $this->tab = request('tab', 'tickets');
    }

    public function resetFilters(): void
    {
        $this->filterAgents     = [];
        $this->filterStatuses   = [];
        $this->filterPriorities = [];
        $this->filterTypes      = [];
        $this->filterSources    = [];
        $this->filterCompanies  = [];
        $this->filterContacts   = [];
        $this->filterTags       = [];
        $this->filterCreatedFrom = '';
        $this->filterCreatedTo   = '';
    }

    public function activeFilterCount(): int
    {
        return count($this->filterAgents)
            + count($this->filterStatuses)
            + count($this->filterPriorities)
            + count($this->filterTypes)
            + count($this->filterSources)
            + count($this->filterCompanies)
            + count($this->filterContacts)
            + count($this->filterTags)
            + ($this->filterCreatedFrom ? 1 : 0)
            + ($this->filterCreatedTo   ? 1 : 0);
    }

    private function ticketsQuery(): \Illuminate\Database\Eloquent\Builder
    {
        $q = trim($this->search);

        return FdTicket::query()
            ->when($q, fn ($x) => $x->where(function ($x) use ($q) {
                $x->where('subject', 'like', "%{$q}%")
                  ->orWhere('description_text', 'like', "%{$q}%");
            }))
            ->when($this->filterAgents,     fn ($x) => $x->whereIn('fd_responder_id', $this->filterAgents))
            ->when($this->filterStatuses,   fn ($x) => $x->whereIn('status', $this->filterStatuses))
            ->when($this->filterPriorities, fn ($x) => $x->whereIn('priority', $this->filterPriorities))
            ->when($this->filterTypes,      fn ($x) => $x->whereIn('type', $this->filterTypes))
            ->when($this->filterSources,    fn ($x) => $x->whereIn('source', $this->filterSources))
            ->when($this->filterCompanies,  fn ($x) => $x->whereIn('fd_company_id', $this->filterCompanies))
            ->when($this->filterContacts,   fn ($x) => $x->whereIn('fd_requester_id', $this->filterContacts))
            ->when($this->filterTags, fn ($x) => $x->where(function ($q) {
                foreach ($this->filterTags as $tag) {
                    $q->orWhereJsonContains('tags', $tag);
                }
            }))
            ->when($this->filterCreatedFrom, fn ($x) => $x->where('fd_created_at', '>=', $this->filterCreatedFrom))
            ->when($this->filterCreatedTo,   fn ($x) => $x->where('fd_created_at', '<=', $this->filterCreatedTo.' 23:59:59'));
    }

    public function render(): \Illuminate\View\View
    {
        $q = trim($this->search);

        $companies = $contacts = $tickets = $comments = null;

        if ($this->tab === 'companies') {
            $companies = FdCompany::query()
                ->when($q, fn ($x) => $x->where('name', 'like', "%{$q}%"))
                ->withCount('tickets')
                ->orderBy('name')
                ->paginate(25);
        }

        if ($this->tab === 'contacts') {
            $contacts = FdContact::query()
                ->when($q, fn ($x) => $x->where(function ($x) use ($q) {
                    $x->where('name', 'like', "%{$q}%")
                      ->orWhere('email', 'like', "%{$q}%");
                }))
                ->withCount('tickets')
                ->orderBy('name')
                ->paginate(25);
        }

        if ($this->tab === 'tickets') {
            $tickets = $this->ticketsQuery()
                ->with('company:fd_id,name')
                ->withCount('comments')
                ->orderByDesc('fd_created_at')
                ->paginate(25);
        }

        if ($this->tab === 'comments') {
            $comments = FdComment::query()
                ->when($q, fn ($x) => $x->where(function ($x) use ($q) {
                    $x->where('body_text', 'like', "%{$q}%")
                      ->orWhere('from_email', 'like', "%{$q}%");
                }))
                ->with('ticket:fd_id,subject')
                ->orderByDesc('fd_created_at')
                ->paginate(25);
        }

        $agents    = FdAgent::orderBy('name')->get(['fd_id', 'name', 'email']);
        $companies_list = FdCompany::orderBy('name')->get(['fd_id', 'name']);
        $typeOptions = FdTicket::whereNotNull('type')->distinct()->orderBy('type')->pluck('type');
        $allTags = \Illuminate\Support\Facades\Cache::remember('fd_all_tags', 3600, function () {
            return FdTicket::whereNotNull('tags')
                ->where('tags', '!=', '[]')
                ->pluck('tags')
                ->flatten()
                ->filter()
                ->unique()
                ->sort()
                ->values()
                ->toArray();
        });

        return view('livewire.archive.search', [
            'companies'      => $companies,
            'contacts'       => $contacts,
            'tickets'        => $tickets,
            'comments'       => $comments,
            'agents'         => $agents,
            'companiesList'  => $companies_list,
            'typeOptions'    => $typeOptions,
            'statusMap'      => FdTicket::STATUS_MAP,
            'priorityMap'    => FdTicket::PRIORITY_MAP,
            'sourceMap'      => [1 => 'Email', 2 => 'Portal', 3 => 'Phone', 7 => 'Chat', 9 => 'Feedback Widget', 10 => 'API'],
            'allTags'        => $allTags,
            'activeFilters'  => $this->activeFilterCount(),
        ]);
    }
}
