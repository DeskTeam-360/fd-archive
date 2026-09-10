<div class="space-y-5">

    {{-- Header --}}
    <div class="bg-white dark:bg-neutral-800 rounded-xl border border-neutral-200 dark:border-neutral-700 p-5">
        <div class="flex items-start justify-between">
            <div>
                <h1 class="text-xl font-bold">{{ $company->name }}</h1>
                <div class="text-xs text-neutral-400 mt-1">FD ID: {{ $company->fd_id }}</div>
            </div>
            <div class="text-right text-xs text-neutral-400 space-y-1">
                <div>Last Sync: {{ $company->last_synced_at?->diffForHumans() ?? '—' }}</div>
                <div>FD Created: {{ $company->fd_created_at?->format('Y-m-d') ?? '—' }}</div>
            </div>
        </div>
        @if($company->domains)
        <div class="mt-3 text-sm text-neutral-500">🌐 {{ $company->domains }}</div>
        @endif
        @if($company->description)
        <div class="mt-2 text-sm text-neutral-600 dark:text-neutral-300">{{ $company->description }}</div>
        @endif
        @if($company->note)
        <div class="mt-2 text-xs text-neutral-400 italic">{{ $company->note }}</div>
        @endif
    </div>

    {{-- Tabs --}}
    <div class="flex gap-1 border-b border-neutral-200 dark:border-neutral-700">
        @foreach(['tickets' => 'Tickets', 'contacts' => 'Contacts'] as $key => $label)
        <button wire:click="$set('tab','{{ $key }}')"
            class="px-4 py-2 text-sm font-medium border-b-2 transition -mb-px {{ $tab === $key ? 'border-purple-600 text-purple-600' : 'border-transparent text-neutral-500 hover:text-neutral-800 dark:hover:text-neutral-200' }}">
            {{ $label }}
        </button>
        @endforeach
    </div>

    {{-- Tickets --}}
    @if($tab === 'tickets' && $tickets)
    <div class="rounded-xl border border-neutral-200 dark:border-neutral-700 overflow-hidden">
        <table class="w-full text-sm">
            <thead class="bg-neutral-50 dark:bg-neutral-800 text-xs uppercase text-neutral-500">
                <tr>
                    <th class="px-4 py-3 text-left">Subject</th>
                    <th class="px-4 py-3 text-center">Status</th>
                    <th class="px-4 py-3 text-center">Priority</th>
                    <th class="px-4 py-3 text-center">Comments</th>
                    <th class="px-4 py-3 text-center">Date</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-neutral-100 dark:divide-neutral-800">
                @forelse($tickets as $ticket)
                <tr class="hover:bg-neutral-50 dark:hover:bg-neutral-800/50">
                    <td class="px-4 py-3">
                        <a href="/archive/tickets/{{ $ticket->fd_id }}" class="font-medium text-purple-600 hover:underline">{{ $ticket->subject }}</a>
                        <div class="text-xs text-neutral-400">#{{ $ticket->fd_id }}</div>
                    </td>
                    <td class="px-4 py-3 text-center">
                        @php $color = match($ticket->status) { 2=>'bg-blue-100 text-blue-700', 3=>'bg-yellow-100 text-yellow-700', 4,5=>'bg-green-100 text-green-700', default=>'bg-neutral-100 text-neutral-600' }; @endphp
                        <span class="text-xs px-2 py-0.5 rounded-full font-medium {{ $color }}">{{ $ticket->status_label }}</span>
                    </td>
                    <td class="px-4 py-3 text-center text-xs text-neutral-500">{{ $ticket->priority_label }}</td>
                    <td class="px-4 py-3 text-center text-xs text-neutral-500">{{ $ticket->comments_count }}</td>
                    <td class="px-4 py-3 text-center text-xs text-neutral-400">{{ $ticket->fd_created_at?->format('Y-m-d') }}</td>
                </tr>
                @empty
                <tr><td colspan="5" class="px-4 py-8 text-center text-neutral-400 text-sm">No tickets</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
    <div>{{ $tickets->links() }}</div>
    @endif

    {{-- Contacts --}}
    @if($tab === 'contacts' && $contacts)
    <div class="rounded-xl border border-neutral-200 dark:border-neutral-700 overflow-hidden">
        <table class="w-full text-sm">
            <thead class="bg-neutral-50 dark:bg-neutral-800 text-xs uppercase text-neutral-500">
                <tr>
                    <th class="px-4 py-3 text-left">Name</th>
                    <th class="px-4 py-3 text-left">Email</th>
                    <th class="px-4 py-3 text-left">Job Title</th>
                    <th class="px-4 py-3 text-center">Tickets</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-neutral-100 dark:divide-neutral-800">
                @forelse($contacts as $contact)
                <tr class="hover:bg-neutral-50 dark:hover:bg-neutral-800/50">
                    <td class="px-4 py-3">
                        <a href="/archive/contacts/{{ $contact->fd_id }}" class="font-medium text-purple-600 hover:underline">{{ $contact->name }}</a>
                    </td>
                    <td class="px-4 py-3 text-xs text-neutral-500">{{ $contact->email ?: '—' }}</td>
                    <td class="px-4 py-3 text-xs text-neutral-500">{{ $contact->job_title ?: '—' }}</td>
                    <td class="px-4 py-3 text-center">
                        <span class="bg-blue-100 dark:bg-blue-900/30 text-blue-700 text-xs px-2 py-0.5 rounded-full font-medium">{{ $contact->tickets_count }}</span>
                    </td>
                </tr>
                @empty
                <tr><td colspan="4" class="px-4 py-8 text-center text-neutral-400 text-sm">No contacts</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
    <div>{{ $contacts->links() }}</div>
    @endif

</div>
