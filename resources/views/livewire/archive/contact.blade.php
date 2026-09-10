<div class="space-y-5">

    {{-- Header --}}
    <div class="bg-white dark:bg-neutral-800 rounded-xl border border-neutral-200 dark:border-neutral-700 p-5">
        <div class="flex items-start justify-between">
            <div>
                <h1 class="text-xl font-bold">{{ $contact->name }}</h1>
                <div class="text-xs text-neutral-400 mt-1">FD ID: {{ $contact->fd_id }}</div>
            </div>
            <div class="text-right text-xs text-neutral-400 space-y-1">
                <div>Last Sync: {{ $contact->last_synced_at?->diffForHumans() ?? '—' }}</div>
                <div>FD Created: {{ $contact->fd_created_at?->format('Y-m-d') ?? '—' }}</div>
            </div>
        </div>
        <div class="mt-4 grid grid-cols-2 md:grid-cols-3 gap-3 text-sm">
            <div>
                <div class="text-xs text-neutral-400 mb-0.5">Email</div>
                <div>{{ $contact->email ?: '—' }}</div>
            </div>
            <div>
                <div class="text-xs text-neutral-400 mb-0.5">Phone</div>
                <div>{{ $contact->phone ?: $contact->mobile ?: '—' }}</div>
            </div>
            <div>
                <div class="text-xs text-neutral-400 mb-0.5">Job Title</div>
                <div>{{ $contact->job_title ?: '—' }}</div>
            </div>
            <div>
                <div class="text-xs text-neutral-400 mb-0.5">Company</div>
                <div>
                    @if($contact->company)
                        <a href="/archive/companies/{{ $contact->company->fd_id }}" class="text-purple-600 hover:underline">{{ $contact->company->name }}</a>
                    @else
                        <span class="text-neutral-400">No company</span>
                    @endif
                </div>
            </div>
            <div>
                <div class="text-xs text-neutral-400 mb-0.5">Language</div>
                <div>{{ $contact->language ?: '—' }}</div>
            </div>
            <div>
                <div class="text-xs text-neutral-400 mb-0.5">Timezone</div>
                <div>{{ $contact->time_zone ?: '—' }}</div>
            </div>
        </div>
    </div>

    {{-- Tickets --}}
    <h2 class="font-semibold text-base">Tickets ({{ $tickets->total() }})</h2>
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

</div>
