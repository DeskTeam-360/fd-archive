<?php

namespace App\Livewire\Archive;

use App\Models\FdTicket;
use Livewire\Component;

class ArchiveTicket extends Component
{
    public int $ticketId;

    public function render(): \Illuminate\View\View
    {
        $ticket = FdTicket::with([
            'company:fd_id,name',
            'requester:fd_id,name,email',
            'requesterAgent:fd_id,name,email',
            'responderAgent:fd_id,name,email',
            'comments' => fn ($q) => $q->orderBy('fd_created_at'),
        ])->findOrFail($this->ticketId);

        return view('livewire.archive.ticket', compact('ticket'));
    }
}
