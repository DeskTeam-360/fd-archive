<x-layouts::app :title="__('Tickets — Archive')">
<div class="flex gap-6 p-6 min-h-full">

    {{-- SIDEBAR FILTERS --}}
    <form method="GET" action="{{ route('archive.tickets') }}" id="filter-form" class="w-60 shrink-0">

        <div class="space-y-3">

            <div class="flex items-center justify-between">
                <span class="text-sm font-semibold">Filters</span>
                @if(request()->hasAny(['search','agents','statuses','priorities','types','sources','companies','tags','date_from','date_to']))
                    <a href="{{ route('archive.tickets') }}" class="text-xs text-red-500 hover:text-red-700">Clear all</a>
                @endif
            </div>

            {{-- Search --}}
            <div class="space-y-1">
                <div class="text-xs font-semibold uppercase tracking-wide text-neutral-500">Search</div>
                <input name="search" value="{{ $fSearch }}" type="text" placeholder="Keyword..."
                    class="w-full rounded-lg border border-neutral-300 bg-white px-3 py-1.5 text-sm focus:outline-none focus:ring-2 focus:ring-purple-500" />
            </div>

            {{-- Agent --}}
            <div class="space-y-1">
                <div class="text-xs font-semibold uppercase tracking-wide text-neutral-500">Agent</div>
                <select name="agents[]" id="ts-agents" multiple class="w-full">
                    @foreach($agents as $agent)
                        <option value="{{ $agent->fd_id }}" {{ in_array($agent->fd_id, $fAgents) ? 'selected' : '' }}>
                            {{ $agent->name }}
                        </option>
                    @endforeach
                </select>
            </div>

            {{-- Status --}}
            <div class="space-y-1">
                <div class="text-xs font-semibold uppercase tracking-wide text-neutral-500">Status</div>
                <select name="statuses[]" id="ts-statuses" multiple class="w-full">
                    @foreach($statusMap as $val => $label)
                        <option value="{{ $val }}" {{ in_array($val, $fStatuses) ? 'selected' : '' }}>{{ $label }}</option>
                    @endforeach
                </select>
            </div>

            {{-- Priority --}}
            <div class="space-y-1">
                <div class="text-xs font-semibold uppercase tracking-wide text-neutral-500">Priority</div>
                <select name="priorities[]" id="ts-priorities" multiple class="w-full">
                    @foreach($priorityMap as $val => $label)
                        <option value="{{ $val }}" {{ in_array($val, $fPriorities) ? 'selected' : '' }}>{{ $label }}</option>
                    @endforeach
                </select>
            </div>

            {{-- Type --}}
            <div class="space-y-1">
                <div class="text-xs font-semibold uppercase tracking-wide text-neutral-500">Type</div>
                <select name="types[]" id="ts-types" multiple class="w-full">
                    @foreach($typeOptions as $type)
                        <option value="{{ $type }}" {{ in_array($type, $fTypes) ? 'selected' : '' }}>{{ $type }}</option>
                    @endforeach
                </select>
            </div>

            {{-- Source --}}
            <div class="space-y-1">
                <div class="text-xs font-semibold uppercase tracking-wide text-neutral-500">Source</div>
                <select name="sources[]" id="ts-sources" multiple class="w-full">
                    @foreach($sourceMap as $val => $label)
                        <option value="{{ $val }}" {{ in_array($val, $fSources) ? 'selected' : '' }}>{{ $label }}</option>
                    @endforeach
                </select>
            </div>

            {{-- Company --}}
            <div class="space-y-1">
                <div class="text-xs font-semibold uppercase tracking-wide text-neutral-500">Company</div>
                <select name="companies[]" id="ts-companies" multiple class="w-full">
                    @foreach($companiesList as $company)
                        <option value="{{ $company->fd_id }}" {{ in_array($company->fd_id, $fCompanies) ? 'selected' : '' }}>
                            {{ $company->name }}
                        </option>
                    @endforeach
                </select>
            </div>

            {{-- Tags --}}
            <div class="space-y-1">
                <div class="text-xs font-semibold uppercase tracking-wide text-neutral-500">Tags</div>
                <select name="tags[]" id="ts-tags" multiple class="w-full">
                    @foreach($allTags as $tag)
                        <option value="{{ $tag }}" {{ in_array($tag, $fTags) ? 'selected' : '' }}>{{ $tag }}</option>
                    @endforeach
                </select>
            </div>

            {{-- Created date range --}}
            <div class="space-y-1">
                <div class="text-xs font-semibold uppercase tracking-wide text-neutral-500">Created</div>
                <input name="date_from" value="{{ $fDateFrom }}" type="date"
                    class="w-full rounded-lg border border-neutral-300 bg-white px-2 py-1.5 text-xs" />
                <input name="date_to" value="{{ $fDateTo }}" type="date"
                    class="w-full rounded-lg border border-neutral-300 bg-white px-2 py-1.5 text-xs" />
            </div>

            <button type="submit"
                class="w-full bg-purple-600 hover:bg-purple-700 text-white text-sm font-medium py-2 rounded-lg transition-colors">
                Apply Filters
            </button>

        </div>
    </form>

    {{-- MAIN CONTENT --}}
    <div class="flex-1 min-w-0 space-y-4">

        {{-- Active filter chips --}}
        @php
            $hasFilters = $fSearch || $fAgents || $fStatuses || $fPriorities || $fTypes || $fSources || $fCompanies || $fTags || $fDateFrom || $fDateTo;
        @endphp
        @if($hasFilters)
        <div class="flex flex-wrap gap-2">
            @if($fSearch)
                <span class="inline-flex items-center gap-1 bg-neutral-100 text-neutral-700 text-xs px-2 py-1 rounded-full">
                    🔍 {{ $fSearch }}
                </span>
            @endif
            @foreach($fStatuses as $s)
                <span class="inline-flex items-center gap-1 bg-blue-100 text-blue-700 text-xs px-2 py-1 rounded-full">{{ $statusMap[$s] ?? $s }}</span>
            @endforeach
            @foreach($fPriorities as $p)
                <span class="inline-flex items-center gap-1 bg-orange-100 text-orange-700 text-xs px-2 py-1 rounded-full">{{ $priorityMap[$p] ?? $p }}</span>
            @endforeach
            @foreach($fTypes as $t)
                <span class="inline-flex items-center gap-1 bg-purple-100 text-purple-700 text-xs px-2 py-1 rounded-full">{{ $t }}</span>
            @endforeach
            @foreach($fAgents as $a)
                @php $ag = $agents->firstWhere('fd_id', $a); @endphp
                <span class="inline-flex items-center gap-1 bg-indigo-100 text-indigo-700 text-xs px-2 py-1 rounded-full">{{ $ag?->name ?? $a }}</span>
            @endforeach
            @foreach($fCompanies as $c)
                @php $co = $companiesList->firstWhere('fd_id', $c); @endphp
                <span class="inline-flex items-center gap-1 bg-teal-100 text-teal-700 text-xs px-2 py-1 rounded-full">{{ $co?->name ?? $c }}</span>
            @endforeach
            @foreach($fTags as $t)
                <span class="inline-flex items-center gap-1 bg-green-100 text-green-700 text-xs px-2 py-1 rounded-full">🏷 {{ $t }}</span>
            @endforeach
            @if($fDateFrom || $fDateTo)
                <span class="inline-flex items-center gap-1 bg-neutral-100 text-neutral-600 text-xs px-2 py-1 rounded-full">
                    📅 {{ $fDateFrom ?: '…' }} → {{ $fDateTo ?: '…' }}
                </span>
            @endif
        </div>
        @endif

        <div class="text-xs text-neutral-400">{{ number_format($tickets->total()) }} tickets found</div>

        <div class="rounded-xl border border-neutral-200 overflow-hidden">
            <table class="w-full text-sm">
                <thead class="bg-neutral-50 text-xs uppercase text-neutral-500">
                    <tr>
                        <th class="px-4 py-3 text-left">Subject</th>
                        <th class="px-4 py-3 text-left">Company</th>
                        <th class="px-4 py-3 text-center">Status</th>
                        <th class="px-4 py-3 text-center">Priority</th>
                        <th class="px-4 py-3 text-center">💬</th>
                        <th class="px-4 py-3 text-center">Date</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-neutral-100">
                    @forelse($tickets as $ticket)
                    <tr class="hover:bg-neutral-50">
                        <td class="px-4 py-3 max-w-xs">
                            <a href="{{ route('archive.ticket', $ticket->fd_id) }}" class="font-medium text-purple-600 hover:underline line-clamp-2">{{ $ticket->subject }}</a>
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

<script>
    document.addEventListener('DOMContentLoaded', function () {
        [
            'ts-agents', 'ts-statuses', 'ts-priorities',
            'ts-types', 'ts-sources', 'ts-companies', 'ts-tags'
        ].forEach(function (id) {
            var el = document.getElementById(id);
            if (el && window.TomSelect) {
                new TomSelect(el, {
                    plugins: ['remove_button', 'checkbox_options'],
                    maxOptions: 300,
                });
            }
        });
    });
</script>
</x-layouts::app>
