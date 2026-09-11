<?php

namespace App\Livewire\Archive;

use App\Models\FdContact;
use Livewire\Attributes\Layout;
use Livewire\Component;
use Livewire\WithPagination;

#[Layout('layouts.app', ['title' => 'Contacts — Archive'])]
class ArchiveContacts extends Component
{
    use WithPagination;

    public string $search = '';

    public function updatingSearch(): void { $this->resetPage(); }

    public function render()
    {
        $q = trim($this->search);

        $contacts = FdContact::query()
            ->when($q, fn ($x) => $x->where(fn ($x) => $x
                ->where('name', 'like', "%{$q}%")
                ->orWhere('email', 'like', "%{$q}%")))
            ->withCount('tickets')
            ->orderBy('name')
            ->paginate(25);

        return view('livewire.archive.contacts', compact('contacts'));
    }
}
