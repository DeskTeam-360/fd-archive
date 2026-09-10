<div class="flex gap-5">

    {{-- SIDEBAR FILTERS (tickets tab only) --}}
    @if($tab === 'tickets')
    <div class="w-56 shrink-0 space-y-3">

        <div class="flex items-center justify-between">
            <span class="text-sm font-semibold">Filters</span>
            @if($activeFilters > 0)
                <button wire:click="resetFilters" class="text-xs text-red-500 hover:text-red-700">Clear ({{ $activeFilters }})</button>
            @endif
        </div>

        {{-- Agent --}}
        <div class="space-y-1">
            <div class="text-xs font-semibold uppercase tracking-wide text-neutral-500">Agent</div>
            <select multiple data-ts-prop="filterAgents" class="w-full">
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
            <select multiple data-ts-prop="filterStatuses" class="w-full">
                @foreach($statusMap as $val => $label)
                    <option value="{{ $val }}" {{ in_array($val, $filterStatuses) ? 'selected' : '' }}>{{ $label }}</option>
                @endforeach
            </select>
        </div>

        {{-- Priority --}}
        <div class="space-y-1">
            <div class="text-xs font-semibold uppercase tracking-wide text-neutral-500">Priority</div>
            <select multiple data-ts-prop="filterPriorities" class="w-full">
                @foreach($priorityMap as $val => $label)
                    <option value="{{ $val }}" {{ in_array($val, $filterPriorities) ? 'selected' : '' }}>{{ $label }}</option>
                @endforeach
            </select>
        </div>

        {{-- Type --}}
        <div class="space-y-1">
            <div class="text-xs font-semibold uppercase tracking-wide text-neutral-500">Type</div>
            <select multiple data-ts-prop="filterTypes" class="w-full">
                @foreach($typeOptions as $type)
                    <option value="{{ $type }}" {{ in_array($type, $filterTypes) ? 'selected' : '' }}>{{ $type }}</option>
                @endforeach
            </select>
        </div>

        {{-- Source --}}
        <div class="space-y-1">
            <div class="text-xs font-semibold uppercase tracking-wide text-neutral-500">Source</div>
            <select multiple data-ts-prop="filterSources" class="w-full">
                @foreach($sourceMap as $val => $label)
                    <option value="{{ $val }}" {{ in_array($val, $filterSources) ? 'selected' : '' }}>{{ $label }}</option>
                @endforeach
            </select>
        </div>

        {{-- Company --}}
        <div class="space-y-1">
            <div class="text-xs font-semibold uppercase tracking-wide text-neutral-500">Company</div>
            <select multiple data-ts-prop="filterCompanies" class="w-full">
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
            <select multiple data-ts-prop="filterTags" class="w-full">
                @foreach($allTags as $tag)
                    <option value="{{ $tag }}" {{ in_array($tag, $filterTags) ? 'selected' : '' }}>{{ $tag }}</option>
                @endforeach
            </select>
        </div>

        {{-- Created date range --}}
        <div class="space-y-1">
            <div class="text-xs font-semibold uppercase tracking-wide text-neutral-500">Created</div>
            <input wire:model.live="filterCreatedFrom" type="date" placeholder="From"
                class="w-full rounded-lg border border-neutral-300 dark:border-neutral-600 bg-white dark:bg-neutral-800 px-2 py-1.5 text-xs" />
            <input wire:model.live="filterCreatedTo" type="date" placeholder="To"
                class="w-full rounded-lg border border-neutral-300 dark:border-neutral-600 bg-white dark:bg-neutral-800 px-2 py-1.5 text-xs" />
        </div>

    </div>
    @endif

    {{-- MAIN CONTENT --}}
    <div class="flex-1 min-w-0 space-y-4">

        {{-- Search bar --}}
        <div class="flex gap-3 items-center">
            <input wire:model.live.debounce.300ms="search" type="text"
                placeholder="Search {{ $tab }}..."
                class="flex-1 rounded-xl border border-neutral-300 dark:border-neutral-600 bg-white dark:bg-neutral-800 px-4 py-2.5 text-sm shadow-sm focus:outline-none focus:ring-2 focus:ring-purple-500" />
            @if($search)
                <button wire:click="$set('search','')" class="text-xs text-neutral-400 hover:text-neutral-600 px-2">✕</button>
            @endif
        </div>

        {{-- Active filter chips --}}
        @if($tab === 'tickets' && $activeFilters > 0)
        <div class="flex flex-wrap gap-2">
            @foreach($filterStatuses as $s)
                <span class="inline-flex items-center gap-1 bg-blue-100 text-blue-700 text-xs px-2 py-1 rounded-full">
                    {{ $statusMap[$s] ?? $s }}
                    <button wire:click="$set('filterStatuses', {{ json_encode(array_values(array_filter($filterStatuses, fn($x) => $x != $s))) }})" class="hover:text-blue-900 ml-0.5">×</button>
                </span>
            @endforeach
            @foreach($filterPriorities as $p)
                <span class="inline-flex items-center gap-1 bg-orange-100 text-orange-700 text-xs px-2 py-1 rounded-full">
                    {{ $priorityMap[$p] ?? $p }}
                    <button wire:click="$set('filterPriorities', {{ json_encode(array_values(array_filter($filterPriorities, fn($x) => $x != $p))) }})" class="hover:text-orange-900 ml-0.5">×</button>
                </span>
            @endforeach
            @foreach($filterTypes as $t)
                <span class="inline-flex items-center gap-1 bg-purple-100 text-purple-700 text-xs px-2 py-1 rounded-full">
                    {{ $t }}
                    <button wire:click="$set('filterTypes', {{ json_encode(array_values(array_filter($filterTypes, fn($x) => $x != $t))) }})" class="hover:text-purple-900 ml-0.5">×</button>
                </span>
            @endforeach
            @foreach($filterAgents as $a)
                @php $ag = $agents->firstWhere('fd_id', $a); @endphp
                <span class="inline-flex items-center gap-1 bg-indigo-100 text-indigo-700 text-xs px-2 py-1 rounded-full">
                    {{ $ag?->name ?? $a }}
                    <button wire:click="$set('filterAgents', {{ json_encode(array_values(array_filter($filterAgents, fn($x) => $x != $a))) }})" class="hover:text-indigo-900 ml-0.5">×</button>
                </span>
            @endforeach
            @foreach($filterCompanies as $c)
                @php $co = $companiesList->firstWhere('fd_id', $c); @endphp
                <span class="inline-flex items-center gap-1 bg-teal-100 text-teal-700 text-xs px-2 py-1 rounded-full">
                    {{ $co?->name ?? $c }}
                    <button wire:click="$set('filterCompanies', {{ json_encode(array_values(array_filter($filterCompanies, fn($x) => $x != $c))) }})" class="hover:text-teal-900 ml-0.5">×</button>
                </span>
            @endforeach
            @foreach($filterTags as $t)
                <span class="inline-flex items-center gap-1 bg-green-100 text-green-700 text-xs px-2 py-1 rounded-full">
                    🏷 {{ $t }}
                    <button wire:click="$set('filterTags', {{ json_encode(array_values(array_filter($filterTags, fn($x) => $x !== $t))) }})" class="hover:text-green-900 ml-0.5">×</button>
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

        {{-- Tabs --}}
        <div class="flex gap-1 border-b border-neutral-200 dark:border-neutral-700">
            @foreach(['tickets' => 'Tickets', 'companies' => 'Companies', 'contacts' => 'Contacts', 'comments' => 'Comments'] as $key => $label)
            <button wire:click="$set('tab','{{ $key }}')"
                class="px-4 py-2 text-sm font-medium border-b-2 transition -mb-px {{ $tab === $key ? 'border-purple-600 text-purple-600' : 'border-transparent text-neutral-500 hover:text-neutral-800 dark:hover:text-neutral-200' }}">
                {{ $label }}
            </button>
            @endforeach
        </div>

        {{-- TICKETS --}}
        @if($tab === 'tickets' && $tickets)
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
        @endif

        {{-- COMPANIES --}}
        @if($tab === 'companies' && $companies)
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
        @endif

        {{-- CONTACTS --}}
        @if($tab === 'contacts' && $contacts)
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
        @endif

        {{-- COMMENTS --}}
        @if($tab === 'comments' && $comments)
        <div class="space-y-3">
            @forelse($comments as $comment)
            <div class="rounded-xl border border-neutral-200 dark:border-neutral-700 bg-white dark:bg-neutral-800 p-4">
                <div class="flex items-start justify-between gap-3 mb-2">
                    <div>
                        <a href="/archive/tickets/{{ $comment->fd_ticket_id }}" class="text-sm font-medium text-purple-600 hover:underline">
                            {{ $comment->ticket?->subject ?? '#'.$comment->fd_ticket_id }}
                        </a>
                        <div class="text-xs text-neutral-400 mt-0.5">From: {{ $comment->from_email ?: '—' }} · {{ $comment->fd_created_at?->format('Y-m-d H:i') }}</div>
                    </div>
                    @if($comment->incoming)
                        <span class="text-xs bg-orange-100 text-orange-600 px-2 py-0.5 rounded-full shrink-0">Incoming</span>
                    @else
                        <span class="text-xs bg-blue-100 text-blue-600 px-2 py-0.5 rounded-full shrink-0">Reply</span>
                    @endif
                </div>
                <p class="text-sm text-neutral-600 dark:text-neutral-300 line-clamp-3">{{ $comment->body_text }}</p>
            </div>
            @empty
            <div class="text-center text-neutral-400 text-sm py-8">No comments found</div>
            @endforelse
        </div>
        <div>{{ $comments->links() }}</div>
        @endif

    </div>
</div>
