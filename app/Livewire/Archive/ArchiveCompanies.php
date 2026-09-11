<?php

namespace App\Livewire\Archive;

use App\Models\FdCompany;
use Livewire\Attributes\Layout;
use Livewire\Component;
use Livewire\WithPagination;

#[Layout('layouts.app', ['title' => 'Companies — Archive'])]
class ArchiveCompanies extends Component
{
    use WithPagination;

    public string $search = '';

    public function updatingSearch(): void { $this->resetPage(); }

    public function render()
    {
        $q = trim($this->search);

        $companies = FdCompany::query()
            ->when($q, fn ($x) => $x->where('name', 'like', "%{$q}%"))
            ->withCount('tickets')
            ->orderBy('name')
            ->paginate(25);

        return view('livewire.archive.companies', compact('companies'));
    }
}
