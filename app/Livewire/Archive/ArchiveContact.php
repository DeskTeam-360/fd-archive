<?php

namespace App\Livewire\Archive;

use App\Models\FdContact;
use Livewire\Component;
use Livewire\WithPagination;

class ArchiveContact extends Component
{
    use WithPagination;

    public int $contactId;

    public function render(): \Illuminate\View\View
    {
        $contact = FdContact::with('company:fd_id,name')->findOrFail($this->contactId);

        $tickets = $contact->tickets()
            ->withCount('comments')
            ->orderByDesc('fd_created_at')
            ->paginate(25);

        return view('livewire.archive.contact', compact('contact', 'tickets'));
    }
}
