<div class="p-6 space-y-4">

    {{-- Search bar --}}
    <div class="flex gap-3 items-center">
        <input wire:model.live.debounce.300ms="search" type="text"
            placeholder="Search companies by name..."
            class="flex-1 rounded-xl border border-neutral-300 dark:border-neutral-600 bg-white dark:bg-neutral-800 px-4 py-2.5 text-sm shadow-sm focus:outline-none focus:ring-2 focus:ring-purple-500" />
        @if($search)
            <button wire:click="$set('search','')" class="text-xs text-neutral-400 hover:text-neutral-600 px-2">✕</button>
        @endif
    </div>

    <div class="text-xs text-neutral-400">{{ number_format($companies->total()) }} companies found</div>

    <div class="rounded-xl border border-neutral-200 dark:border-neutral-700 overflow-hidden">
        <table class="w-full text-sm">
            <thead class="bg-neutral-50 dark:bg-neutral-800 text-xs uppercase text-neutral-500">
                <tr>
                    <th class="px-4 py-3 text-left">Company</th>
                    <th class="px-4 py-3 text-left">Domains</th>
                    <th class="px-4 py-3 text-center">Tickets</th>
                    <th class="px-4 py-3 text-center">Last Sync</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-neutral-100 dark:divide-neutral-800">
                @forelse($companies as $company)
                <tr class="hover:bg-neutral-50 dark:hover:bg-neutral-800/50">
                    <td class="px-4 py-3">
                        <a href="/archive/companies/{{ $company->fd_id }}" class="font-medium text-purple-600 hover:underline">{{ $company->name }}</a>
                        <div class="text-xs text-neutral-400">ID: {{ $company->fd_id }}</div>
                    </td>
                    <td class="px-4 py-3 text-xs text-neutral-500">{{ $company->domains ?: '—' }}</td>
                    <td class="px-4 py-3 text-center">
                        <span class="bg-blue-100 text-blue-700 text-xs px-2 py-0.5 rounded-full font-medium">{{ number_format($company->tickets_count) }}</span>
                    </td>
                    <td class="px-4 py-3 text-center text-xs text-neutral-400">{{ $company->last_synced_at?->diffForHumans() ?? '—' }}</td>
                </tr>
                @empty
                <tr><td colspan="4" class="px-4 py-8 text-center text-neutral-400 text-sm">No companies found</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
    <div>{{ $companies->links() }}</div>

</div>
