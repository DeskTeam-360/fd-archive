<div class="p-6 space-y-4">

    {{-- Search bar --}}
    <div class="flex gap-3 items-center">
        <input wire:model.live.debounce.300ms="search" type="text"
            placeholder="Search contacts by name or email..."
            class="flex-1 rounded-xl border border-neutral-300 dark:border-neutral-600 bg-white dark:bg-neutral-800 px-4 py-2.5 text-sm shadow-sm focus:outline-none focus:ring-2 focus:ring-purple-500" />
        @if($search)
            <button wire:click="$set('search','')" class="text-xs text-neutral-400 hover:text-neutral-600 px-2">✕</button>
        @endif
    </div>

    <div class="text-xs text-neutral-400">{{ number_format($contacts->total()) }} contacts found</div>

    <div class="rounded-xl border border-neutral-200 dark:border-neutral-700 overflow-hidden">
        <table class="w-full text-sm">
            <thead class="bg-neutral-50 dark:bg-neutral-800 text-xs uppercase text-neutral-500">
                <tr>
                    <th class="px-4 py-3 text-left">Name</th>
                    <th class="px-4 py-3 text-left">Email</th>
                    <th class="px-4 py-3 text-left">Company</th>
                    <th class="px-4 py-3 text-center">Tickets</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-neutral-100 dark:divide-neutral-800">
                @forelse($contacts as $contact)
                <tr class="hover:bg-neutral-50 dark:hover:bg-neutral-800/50">
                    <td class="px-4 py-3">
                        <a href="/archive/contacts/{{ $contact->fd_id }}" class="font-medium text-purple-600 hover:underline">{{ $contact->name }}</a>
                        <div class="text-xs text-neutral-400">ID: {{ $contact->fd_id }}</div>
                    </td>
                    <td class="px-4 py-3 text-xs text-neutral-500">{{ $contact->email ?: '—' }}</td>
                    <td class="px-4 py-3 text-xs">
                        @if($contact->fd_company_id)
                            <a href="/archive/companies/{{ $contact->fd_company_id }}" class="text-blue-500 hover:underline">{{ $contact->fd_company_id }}</a>
                        @else
                            <span class="text-neutral-400">—</span>
                        @endif
                    </td>
                    <td class="px-4 py-3 text-center">
                        <span class="bg-blue-100 text-blue-700 text-xs px-2 py-0.5 rounded-full font-medium">{{ number_format($contact->tickets_count) }}</span>
                    </td>
                </tr>
                @empty
                <tr><td colspan="4" class="px-4 py-8 text-center text-neutral-400 text-sm">No contacts found</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
    <div>{{ $contacts->links() }}</div>

</div>
