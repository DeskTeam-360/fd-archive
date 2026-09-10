<?php

namespace App\Livewire\Archive;

use App\Models\FdCompany;
use Livewire\Component;
use Livewire\WithPagination;

class ArchiveCompany extends Component
{
    use WithPagination;

    public int $companyId;
    public string $tab = 'tickets'; // tickets | contacts

    public function updatingTab(): void { $this->resetPage(); }

    public function render(): \Illuminate\View\View
    {
        $company = FdCompany::with([])->findOrFail($this->companyId);

        $tickets = $contacts = null;

        if ($this->tab === 'tickets') {
            $tickets = $company->tickets()
                ->withCount('comments')
                ->orderByDesc('fd_created_at')
                ->paginate(25);
        }

        if ($this->tab === 'contacts') {
            $contacts = $company->contacts()
                ->withCount('tickets')
                ->orderBy('name')
                ->paginate(25);
        }

        return view('livewire.archive.company', compact('company', 'tickets', 'contacts'));
    }
}
