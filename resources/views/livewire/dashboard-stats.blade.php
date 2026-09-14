<div class="space-y-6">

    {{-- Stats cards --}}
    <div class="grid grid-cols-2 lg:grid-cols-4 gap-4">
        <div class="rounded-xl border border-neutral-200 dark:border-neutral-700 p-5">
            <div class="text-3xl font-bold text-blue-600">{{ number_format($tickets) }}</div>
            <div class="text-sm text-neutral-500 dark:text-neutral-400 mt-1">Tickets</div>
            @if($lastTicketSync)
            <div class="text-xs text-neutral-400 dark:text-neutral-500 mt-2">Last: {{ \Carbon\Carbon::parse($lastTicketSync)->diffForHumans() }}</div>
            @endif
        </div>
        <div class="rounded-xl border border-neutral-200 dark:border-neutral-700 p-5">
            <div class="text-3xl font-bold text-purple-600">{{ number_format($contacts) }}</div>
            <div class="text-sm text-neutral-500 dark:text-neutral-400 mt-1">Contacts</div>
            <div class="text-xs text-green-500 mt-1">{{ number_format($contactsSynced) }} synced</div>
            @if($lastContactSync)
            <div class="text-xs text-neutral-400 dark:text-neutral-500 mt-1">Last: {{ \Carbon\Carbon::parse($lastContactSync)->diffForHumans() }}</div>
            @endif
        </div>
        <div class="rounded-xl border border-neutral-200 dark:border-neutral-700 p-5">
            <div class="text-3xl font-bold text-orange-500">{{ number_format($companies) }}</div>
            <div class="text-sm text-neutral-500 dark:text-neutral-400 mt-1">Companies</div>
            <div class="text-xs text-green-500 mt-1">{{ number_format($companiesSynced) }} synced</div>
            @if($lastCompanySync)
            <div class="text-xs text-neutral-400 dark:text-neutral-500 mt-1">Last: {{ \Carbon\Carbon::parse($lastCompanySync)->diffForHumans() }}</div>
            @endif
        </div>
        <div class="rounded-xl border border-neutral-200 dark:border-neutral-700 p-5">
            <div class="text-3xl font-bold text-teal-600">{{ number_format($comments) }}</div>
            <div class="text-sm text-neutral-500 dark:text-neutral-400 mt-1">Comments</div>
        </div>
    </div>

    {{-- Quick Sync --}}
    <div class="rounded-xl border border-neutral-200 dark:border-neutral-700 p-5 space-y-3">
        <div class="flex items-center justify-between flex-wrap gap-3">
            <div>
                <div class="font-semibold text-neutral-800 dark:text-neutral-100">Quick Sync</div>
                <div class="text-xs text-neutral-400 mt-0.5">
                    Akan fetch since <span class="font-mono font-medium text-neutral-600 dark:text-neutral-300">{{ $sincePrev }}</span>
                    (last sync − 1 hari) sampai sekarang
                </div>
            </div>
            <button wire:click="quickSync" wire:loading.attr="disabled"
                class="flex items-center gap-2 bg-blue-600 hover:bg-blue-700 disabled:opacity-60 text-white text-sm font-medium px-4 py-2 rounded-lg transition-colors">
                <span wire:loading.remove wire:target="quickSync">🔄 Sync Sekarang</span>
                <span wire:loading wire:target="quickSync">⏳ Syncing...</span>
            </button>
        </div>

        @if($syncLog)
        <div class="bg-neutral-50 dark:bg-neutral-800 border border-neutral-200 dark:border-neutral-700 rounded-lg p-3">
            <pre class="text-xs text-neutral-600 dark:text-neutral-300 whitespace-pre-wrap font-mono">{{ $syncLog }}</pre>
        </div>
        @endif
    </div>

    {{-- Quick links --}}
    <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
        <a href="{{ route('archive') }}" class="flex items-center gap-4 rounded-xl border border-neutral-200 dark:border-neutral-700 p-5 hover:border-purple-300 hover:bg-purple-50 dark:hover:bg-purple-900/20 transition-colors group">
            <div class="text-2xl">📦</div>
            <div>
                <div class="font-semibold text-neutral-800 dark:text-neutral-100 group-hover:text-purple-700 dark:group-hover:text-purple-400">Archive</div>
                <div class="text-sm text-neutral-500 dark:text-neutral-400">Search tickets, contacts & companies</div>
            </div>
        </a>
        <a href="{{ route('freshdesk-import') }}" class="flex items-center gap-4 rounded-xl border border-neutral-200 dark:border-neutral-700 p-5 hover:border-blue-300 hover:bg-blue-50 dark:hover:bg-blue-900/20 transition-colors group">
            <div class="text-2xl">🔄</div>
            <div>
                <div class="font-semibold text-neutral-800 dark:text-neutral-100 group-hover:text-blue-700 dark:group-hover:text-blue-400">Import</div>
                <div class="text-sm text-neutral-500 dark:text-neutral-400">Sync data from Freshdesk</div>
            </div>
        </a>
        <a href="{{ route('users.index') }}" class="flex items-center gap-4 rounded-xl border border-neutral-200 dark:border-neutral-700 p-5 hover:border-green-300 hover:bg-green-50 dark:hover:bg-green-900/20 transition-colors group">
            <div class="text-2xl">👥</div>
            <div>
                <div class="font-semibold text-neutral-800 dark:text-neutral-100 group-hover:text-green-700 dark:group-hover:text-green-400">Users</div>
                <div class="text-sm text-neutral-500 dark:text-neutral-400">Manage team accounts</div>
            </div>
        </a>
    </div>
</div>
