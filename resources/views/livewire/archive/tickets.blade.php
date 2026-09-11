<div class="flex gap-5 p-6">

    {{-- SIDEBAR FILTERS --}}
    <div class="w-56 shrink-0 space-y-3">

        <div class="flex items-center justify-between">
            <span class="text-sm font-semibold">Filters</span>
            @if($activeFilters > 0 || $search)
                <button wire:click="resetFilters" class="text-xs text-red-500 hover:text-red-700">Clear ({{ $activeFilters + ($search ? 1 : 0) }})</button>
            @endif
        </div>

        {{-- Search --}}
        <div class="space-y-1">
            <div class="text-xs font-semibold uppercase tracking-wide text-neutral-500">Search</div>
            <input wire:model="search" type="text"
                placeholder="Keyword..."
                class="w-full rounded-lg border border-neutral-300 dark:border-neutral-600 bg-white dark:bg-neutral-800 px-3 py-1.5 text-sm focus:outline-none focus:ring-2 focus:ring-purple-500" />
        </div>

        {{-- Agent --}}
        <div class="space-y-1">
            <div class="text-xs font-semibold uppercase tracking-wide text-neutral-500">Agent</div>
            <select id="ts-agents" multiple class="w-full">
                @foreach($agents as $agent)
                    <option value="{{ $agent->fd_id }}" {{ in_array($agent->fd_id, $filterAgents) ? 'selected' : '' }}>
                        {{ $agent->name }}
                    </option>
                @endforeach
            </select>
        </div>

        {{-- Status --}}
        <div class="space-y-1">
            <div class="text-xs font-semibold uppercase tracking-wide text-neutral-500">Status</div>
            <select id="ts-statuses" multiple class="w-full">
                @foreach($statusMap as $val => $label)
                    <option value="{{ $val }}" {{ in_array($val, $filterStatuses) ? 'selected' : '' }}>{{ $label }}</option>
                @endforeach
            </select>
        </div>

        {{-- Priority --}}
        <div class="space-y-1">
            <div class="text-xs font-semibold uppercase tracking-wide text-neutral-500">Priority</div>
            <select id="ts-priorities" multiple class="w-full">
                @foreach($priorityMap as $val => $label)
                    <option value="{{ $val }}" {{ in_array($val, $filterPriorities) ? 'selected' : '' }}>{{ $label }}</option>
                @endforeach
            </select>
        </div>

        {{-- Type --}}
        <div class="space-y-1">
            <div class="text-xs font-semibold uppercase tracking-wide text-neutral-500">Type</div>
            <select id="ts-types" multiple class="w-full">
                @foreach($typeOptions as $type)
                    <option value="{{ $type }}" {{ in_array($type, $filterTypes) ? 'selected' : '' }}>{{ $type }}</option>
                @endforeach
            </select>
        </div>

        {{-- Source --}}
        <div class="space-y-1">
            <div class="text-xs font-semibold uppercase tracking-wide text-neutral-500">Source</div>
            <select id="ts-sources" multiple class="w-full">
                @foreach($sourceMap as $val => $label)
                    <option value="{{ $val }}" {{ in_array($val, $filterSources) ? 'selected' : '' }}>{{ $label }}</option>
                @endforeach
            </select>
        </div>

        {{-- Company --}}
        <div class="space-y-1">
            <div class="text-xs font-semibold uppercase tracking-wide text-neutral-500">Company</div>
            <select id="ts-companies" multiple class="w-full">
                @foreach($companiesList as $company)
                    <option value="{{ $company->fd_id }}" {{ in_array($company->fd_id, $filterCompanies) ? 'selected' : '' }}>
                        {{ $company->name }}
                    </option>
                @endforeach
            </select>
        </div>

        {{-- Tags --}}
        <div class="space-y-1">
            <div class="text-xs font-semibold uppercase tracking-wide text-neutral-500">Tags</div>
            <select id="ts-tags" multiple class="w-full">
                @foreach($allTags as $tag)
                    <option value="{{ $tag }}" {{ in_array($tag, $filterTags) ? 'selected' : '' }}>{{ $tag }}</option>
                @endforeach
            </select>
        </div>

        {{-- Created date range --}}
        <div class="space-y-1">
            <div class="text-xs font-semibold uppercase tracking-wide text-neutral-500">Created</div>
            <input wire:model="filterCreatedFrom" type="date" placeholder="From"
                class="w-full rounded-lg border border-neutral-300 dark:border-neutral-600 bg-white dark:bg-neutral-800 px-2 py-1.5 text-xs" />
            <input wire:model="filterCreatedTo" type="date" placeholder="To"
                class="w-full rounded-lg border border-neutral-300 dark:border-neutral-600 bg-white dark:bg-neutral-800 px-2 py-1.5 text-xs" />
        </div>

        <button wire:click="applyFilters"
            class="w-full bg-purple-600 hover:bg-purple-700 text-white text-sm font-medium py-2 rounded-lg transition-colors">
            Apply Filters
        </button>

    </div>

    {{-- MAIN CONTENT --}}
    <div class="flex-1 min-w-0 space-y-4">

        {{-- Active filter chips --}}
        @if($activeFilters > 0 || $search)
        <div class="flex flex-wrap gap-2">
            @if($search)
                <span class="inline-flex items-center gap-1 bg-neutral-100 text-neutral-700 text-xs px-2 py-1 rounded-full">
                    🔍 {{ $search }}
                    <button wire:click="$set('search','')" class="hover:text-neutral-900 ml-0.5">×</button>
                </span>
            @endif
            @foreach($filterStatuses as $s)
                <span class="inline-flex items-center gap-1 bg-blue-100 text-blue-700 text-xs px-2 py-1 rounded-full">
                    {{ $statusMap[$s] ?? $s }}
                    <button wire:click="removeFilter('filterStatuses', '{{ $s }}')" class="hover:text-blue-900 ml-0.5">×</button>
                </span>
            @endforeach
            @foreach($filterPriorities as $p)
                <span class="inline-flex items-center gap-1 bg-orange-100 text-orange-700 text-xs px-2 py-1 rounded-full">
                    {{ $priorityMap[$p] ?? $p }}
                    <button wire:click="removeFilter('filterPriorities', '{{ $p }}')" class="hover:text-orange-900 ml-0.5">×</button>
                </span>
            @endforeach
            @foreach($filterTypes as $t)
                <span class="inline-flex items-center gap-1 bg-purple-100 text-purple-700 text-xs px-2 py-1 rounded-full">
                    {{ $t }}
                    <button wire:click="removeFilter('filterTypes', '{{ $t }}')" class="hover:text-purple-900 ml-0.5">×</button>
                </span>
            @endforeach
            @foreach($filterAgents as $a)
                @php $ag = $agents->firstWhere('fd_id', $a); @endphp
                <span class="inline-flex items-center gap-1 bg-indigo-100 text-indigo-700 text-xs px-2 py-1 rounded-full">
                    {{ $ag?->name ?? $a }}
                    <button wire:click="removeFilter('filterAgents', '{{ $a }}')" class="hover:text-indigo-900 ml-0.5">×</button>
                </span>
            @endforeach
            @foreach($filterCompanies as $c)
                @php $co = $companiesList->firstWhere('fd_id', $c); @endphp
                <span class="inline-flex items-center gap-1 bg-teal-100 text-teal-700 text-xs px-2 py-1 rounded-full">
                    {{ $co?->name ?? $c }}
                    <button wire:click="removeFilter('filterCompanies', '{{ $c }}')" class="hover:text-teal-900 ml-0.5">×</button>
                </span>
            @endforeach
            @foreach($filterTags as $t)
                <span class="inline-flex items-center gap-1 bg-green-100 text-green-700 text-xs px-2 py-1 rounded-full">
                    🏷 {{ $t }}
                    <button wire:click="removeFilter('filterTags', '{{ $t }}')" class="hover:text-green-900 ml-0.5">×</button>
                </span>
            @endforeach
            @if($filterCreatedFrom || $filterCreatedTo)
                <span class="inline-flex items-center gap-1 bg-neutral-100 text-neutral-600 text-xs px-2 py-1 rounded-full">
                    📅 {{ $filterCreatedFrom ?: '…' }} → {{ $filterCreatedTo ?: '…' }}
                    <button wire:click="$set('filterCreatedFrom',''); $set('filterCreatedTo','')" class="hover:text-neutral-900 ml-0.5">×</button>
                </span>
            @endif
        </div>
        @endif

        <div class="text-xs text-neutral-400">{{ number_format($tickets->total()) }} tickets found</div>

        <div class="rounded-xl border border-neutral-200 dark:border-neutral-700 overflow-hidden">
            <table class="w-full text-sm">
                <thead class="bg-neutral-50 dark:bg-neutral-800 text-xs uppercase text-neutral-500">
                    <tr>
                        <th class="px-4 py-3 text-left">Subject</th>
                        <th class="px-4 py-3 text-left">Company</th>
                        <th class="px-4 py-3 text-center">Status</th>
                        <th class="px-4 py-3 text-center">Priority</th>
                        <th class="px-4 py-3 text-center">💬</th>
                        <th class="px-4 py-3 text-center">Date</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-neutral-100 dark:divide-neutral-800">
                    @forelse($tickets as $ticket)
                    <tr class="hover:bg-neutral-50 dark:hover:bg-neutral-800/50">
                        <td class="px-4 py-3 max-w-xs">
                            <a href="/archive/tickets/{{ $ticket->fd_id }}" class="font-medium text-purple-600 hover:underline line-clamp-2">{{ $ticket->subject }}</a>
                            <div class="text-xs text-neutral-400">#{{ $ticket->fd_id }}</div>
                        </td>
                        <td class="px-4 py-3 text-xs text-neutral-500 max-w-[130px] truncate">{{ $ticket->company?->name ?? '—' }}</td>
                        <td class="px-4 py-3 text-center">
                            @php
                                $sc = match($ticket->status) {
                                    2 => 'bg-blue-100 text-blue-700',
                                    3 => 'bg-yellow-100 text-yellow-700',
                                    4,5 => 'bg-green-100 text-green-700',
                                    10,11,12,13,14 => 'bg-purple-100 text-purple-700',
                                    default => 'bg-neutral-100 text-neutral-600',
                                };
                            @endphp
                            <span class="text-xs px-2 py-0.5 rounded-full font-medium whitespace-nowrap {{ $sc }}">{{ $ticket->status_label }}</span>
                        </td>
                        <td class="px-4 py-3 text-center text-xs text-neutral-500">{{ $ticket->priority_label }}</td>
                        <td class="px-4 py-3 text-center text-xs text-neutral-400">{{ $ticket->comments_count }}</td>
                        <td class="px-4 py-3 text-center text-xs text-neutral-400 whitespace-nowrap">{{ $ticket->fd_created_at?->format('Y-m-d') }}</td>
                    </tr>
                    @empty
                    <tr><td colspan="6" class="px-4 py-8 text-center text-neutral-400 text-sm">No tickets found</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        <div>{{ $tickets->links() }}</div>

    </div>
</div>

@script
<script>
    const selects = [
        { id: 'ts-agents',     prop: 'filterAgents' },
        { id: 'ts-statuses',   prop: 'filterStatuses' },
        { id: 'ts-priorities', prop: 'filterPriorities' },
        { id: 'ts-types',      prop: 'filterTypes' },
        { id: 'ts-sources',    prop: 'filterSources' },
        { id: 'ts-companies',  prop: 'filterCompanies' },
        { id: 'ts-tags',       prop: 'filterTags' },
    ];

    let _syncing = false;
    const instances = {};

    selects.forEach(({ id, prop }) => {
        const el = document.getElementById(id);
        if (!el) return;
        if (el._tomSelect) { el._tomSelect.destroy(); el._tomSelect = null; }

        const ts = new TomSelect(el, {
            plugins: ['remove_button', 'checkbox_options'],
            maxOptions: 300,
            onChange(val) {
                if (_syncing) return;
                _syncing = true;
                $wire.set(prop, Array.isArray(val) ? val : (val ? [val] : []));
                setTimeout(() => { _syncing = false; }, 300);
            },
        });
        el._tomSelect = ts;
        instances[prop] = ts;
    });

    // Clear all TomSelects when resetFilters fires
    Livewire.on('ts-clear-all', () => {
        _syncing = true;
        Object.values(instances).forEach(ts => ts.clear(true));
        setTimeout(() => { _syncing = false; }, 300);
    });

    // Sync TomSelect when individual chip is removed
    Livewire.on('ts-sync', (data) => {
        const { prop, values } = Array.isArray(data) ? data[0] : data;
        if (!instances[prop]) return;
        _syncing = true;
        instances[prop].clear(true);
        (values || []).forEach(v => instances[prop].addItem(String(v), true));
        setTimeout(() => { _syncing = false; }, 300);
    });
</script>
@endscript
