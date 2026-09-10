<div class="space-y-6">

    {{-- Stats --}}
    <div class="grid grid-cols-2 md:grid-cols-3 gap-4">
        <div class="rounded-xl border border-neutral-200 dark:border-neutral-700 p-4 text-center">
            <div class="text-2xl font-bold text-blue-500">{{ number_format($stats['companies']) }}</div>
            <div class="text-sm text-neutral-500 mt-1">Companies</div>
        </div>
        <div class="rounded-xl border border-neutral-200 dark:border-neutral-700 p-4 text-center">
            <div class="text-2xl font-bold text-purple-500">{{ number_format($stats['contacts']) }}</div>
            <div class="text-xs text-neutral-500 mt-1">
                Contacts
                <span class="text-green-500 block">{{ number_format($stats['contacts_synced']) }} synced</span>
                <span class="text-orange-400 block">{{ number_format($stats['contacts'] - $stats['contacts_synced']) }} pending</span>
            </div>
        </div>
        <div class="rounded-xl border border-neutral-200 dark:border-neutral-700 p-4 text-center">
            <div class="text-2xl font-bold text-orange-500">{{ number_format($stats['tickets']) }}</div>
            <div class="text-sm text-neutral-500 mt-1">Tickets</div>
        </div>
        <div class="rounded-xl border border-neutral-200 dark:border-neutral-700 p-4 text-center">
            <div class="text-2xl font-bold text-teal-500">{{ number_format($stats['comments']) }}</div>
            <div class="text-sm text-neutral-500 mt-1">Comments</div>
        </div>
        <div class="rounded-xl border border-neutral-200 dark:border-neutral-700 p-4 text-center">
            <div class="text-2xl font-bold text-green-500">{{ number_format($stats['companies_synced']) }}</div>
            <div class="text-sm text-neutral-500 mt-1">Companies Synced</div>
        </div>
    </div>

    {{-- General Import --}}
    <div class="rounded-xl border border-neutral-200 dark:border-neutral-700 p-5 space-y-4">
        <h2 class="font-semibold text-lg">General Import</h2>
        <div class="grid grid-cols-1 md:grid-cols-3 gap-3">
            <div>
                <label class="text-sm text-neutral-500 mb-1 block">Type</label>
                <select wire:model="importType" class="w-full rounded-lg border border-neutral-300 dark:border-neutral-600 bg-white dark:bg-neutral-800 px-3 py-2 text-sm">
                    <option value="companies">Companies</option>
                    <option value="contacts">Contacts</option>
                    <option value="tickets">Tickets</option>
                    <option value="comments">Comments</option>
                    <option value="all">All</option>
                </select>
            </div>
            <div>
                <label class="text-sm text-neutral-500 mb-1 block">Since (updated_since)</label>
                <input wire:model="since" type="date" class="w-full rounded-lg border border-neutral-300 dark:border-neutral-600 bg-white dark:bg-neutral-800 px-3 py-2 text-sm" />
            </div>
            <div>
                <label class="text-sm text-neutral-500 mb-1 block">Until</label>
                <input wire:model="until" type="date" class="w-full rounded-lg border border-neutral-300 dark:border-neutral-600 bg-white dark:bg-neutral-800 px-3 py-2 text-sm" />
            </div>
        </div>
        <button wire:click="runImport" wire:loading.attr="disabled"
            class="bg-blue-600 hover:bg-blue-700 disabled:opacity-50 text-white px-5 py-2 rounded-lg text-sm font-medium transition">
            <span wire:loading.remove wire:target="runImport">▶ Run Import</span>
            <span wire:loading wire:target="runImport">⏳ Running...</span>
        </button>
    </div>

    {{-- Import by Contact --}}
    <div class="rounded-xl border border-neutral-200 dark:border-neutral-700 p-5 space-y-4">
        <h2 class="font-semibold text-lg">Import by Contact <span class="text-sm font-normal text-neutral-500">(requester_id loop)</span></h2>

        <div class="flex flex-wrap gap-4 items-end">
            <div>
                <label class="text-sm text-neutral-500 mb-1 block">Contact FD ID (optional)</label>
                <input wire:model="contactId" type="text" placeholder="Leave blank for all"
                    class="rounded-lg border border-neutral-300 dark:border-neutral-600 bg-white dark:bg-neutral-800 px-3 py-2 text-sm w-48" />
            </div>
            <label class="flex items-center gap-2 text-sm cursor-pointer pb-2">
                <input wire:model="skipImported" type="checkbox" class="rounded" /> Skip already imported
            </label>
            <label class="flex items-center gap-2 text-sm cursor-pointer pb-2">
                <input wire:model="withComments" type="checkbox" class="rounded" /> Include comments
            </label>
            <button wire:click="runImportByContact" wire:loading.attr="disabled"
                style="background:#7c3aed;color:#fff;padding:0.5rem 1.25rem;border-radius:0.5rem;font-size:0.875rem;font-weight:500;border:none;cursor:pointer;opacity:1"
                class="disabled:opacity-50 transition hover:opacity-90">
                <span wire:loading.remove wire:target="runImportByContact">▶ Run All</span>
                <span wire:loading wire:target="runImportByContact">⏳ Running...</span>
            </button>
        </div>

        {{-- Contact Table --}}
        <div class="space-y-3 mt-2">
            <div class="flex flex-wrap gap-3 items-center justify-between">
                <div class="flex gap-3 items-center">
                    <input wire:model.live.debounce.300ms="search" type="text" placeholder="Search name / email..."
                        class="rounded-lg border border-neutral-300 dark:border-neutral-600 bg-white dark:bg-neutral-800 px-3 py-2 text-sm w-64" />
                    <select wire:model.live="filterSynced"
                        class="rounded-lg border border-neutral-300 dark:border-neutral-600 bg-white dark:bg-neutral-800 px-3 py-2 text-sm">
                        <option value="all">All Status</option>
                        <option value="pending">Pending</option>
                        <option value="synced">Synced</option>
                    </select>
                    <select wire:model.live="filterCompany"
                        class="rounded-lg border border-neutral-300 dark:border-neutral-600 bg-white dark:bg-neutral-800 px-3 py-2 text-sm max-w-[200px] truncate">
                        <option value="all">All Companies</option>
                        <option value="no_company">— No Company —</option>
                        @foreach($companies as $company)
                            <option value="{{ $company->fd_id }}">{{ $company->name }}</option>
                        @endforeach
                    </select>
                </div>

                {{-- Bulk action bar --}}
                @if(count($selected) > 0)
                <div class="flex items-center gap-3 bg-purple-50 dark:bg-purple-900/20 border border-purple-200 dark:border-purple-700 rounded-lg px-4 py-2">
                    <span class="text-sm font-medium text-purple-700 dark:text-purple-300">{{ count($selected) }} selected <span class="text-xs font-normal opacity-70">(max 100 per run)</span></span>
                    <button wire:click="importBulk" wire:loading.attr="disabled"
                        style="background:#7c3aed;color:#fff;padding:0.375rem 1rem;border-radius:0.5rem;font-size:0.75rem;font-weight:500;border:none;cursor:pointer"
                        class="disabled:opacity-50 transition hover:opacity-90">
                        <span wire:loading.remove wire:target="importBulk">▶ Import Selected</span>
                        <span wire:loading wire:target="importBulk">⏳ Importing...</span>
                    </button>
                    <button wire:click="$set('selected', [])" class="text-xs text-neutral-500 hover:text-neutral-700">Cancel</button>
                </div>
                @endif
            </div>

            <div class="overflow-x-auto rounded-lg border border-neutral-200 dark:border-neutral-700">
                <table class="w-full text-sm">
                    <thead class="bg-neutral-50 dark:bg-neutral-800 text-neutral-500 text-xs uppercase">
                        <tr>
                            <th class="px-3 py-3 text-center w-8">
                                <input type="checkbox" wire:model.live="selectAll" class="rounded cursor-pointer" />
                            </th>
                            <th class="px-4 py-3 text-left">FD ID</th>
                            <th class="px-4 py-3 text-left">Name</th>
                            <th class="px-4 py-3 text-left">Email</th>
                            <th class="px-4 py-3 text-center">Status</th>
                            <th class="px-4 py-3 text-center">Last Sync</th>
                            <th class="px-4 py-3 text-center">Action</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-neutral-100 dark:divide-neutral-800">
                        @foreach($contacts as $contact)
                        <tr class="hover:bg-neutral-50 dark:hover:bg-neutral-800/50 {{ in_array((string)$contact->fd_id, array_map('strval', $selected)) ? 'bg-purple-50 dark:bg-purple-900/10' : '' }}">
                            <td class="px-3 py-3 text-center">
                                <input type="checkbox" wire:model.live="selected" value="{{ $contact->fd_id }}" class="rounded cursor-pointer" />
                            </td>
                            <td class="px-4 py-3 text-neutral-400 text-xs">{{ $contact->fd_id }}</td>
                            <td class="px-4 py-3 font-medium">{{ $contact->name }}</td>
                            <td class="px-4 py-3 text-neutral-500">{{ $contact->email ?? '—' }}</td>
                            <td class="px-4 py-3 text-center">
                                @if($contact->tickets_imported_at)
                                    <span class="inline-flex items-center gap-1 bg-green-100 dark:bg-green-900/30 text-green-700 dark:text-green-400 text-xs px-2 py-0.5 rounded-full">✓ Synced</span>
                                @else
                                    <span class="inline-flex items-center gap-1 bg-orange-100 dark:bg-orange-900/30 text-orange-600 dark:text-orange-400 text-xs px-2 py-0.5 rounded-full">⏳ Pending</span>
                                @endif
                            </td>
                            <td class="px-4 py-3 text-center text-xs text-neutral-400">
                                {{ $contact->last_synced_at?->diffForHumans() ?? '—' }}
                            </td>
                            <td class="px-4 py-3 text-center">
                                <button
                                    wire:click="importContact({{ $contact->fd_id }})"
                                    wire:loading.attr="disabled"
                                    wire:loading.class="opacity-50 cursor-not-allowed"
                                    wire:target="importContact({{ $contact->fd_id }})"
                                    style="background:#7c3aed;color:#fff;padding:0.375rem 0.75rem;border-radius:0.5rem;font-size:0.75rem;border:none;cursor:pointer"
                                    class="disabled:opacity-50 transition hover:opacity-90"
                                >
                                    <span wire:loading.remove wire:target="importContact({{ $contact->fd_id }})">Import</span>
                                    <span wire:loading wire:target="importContact({{ $contact->fd_id }})">...</span>
                                </button>
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

            <div>{{ $contacts->links() }}</div>
        </div>
    </div>

    {{-- Log Output --}}
    @if($log)
    <div class="rounded-xl border border-neutral-200 dark:border-neutral-700 p-5">
        <h2 class="font-semibold text-lg mb-3">Output</h2>
        <pre class="bg-neutral-950 text-green-400 text-xs rounded-lg p-4 overflow-x-auto whitespace-pre-wrap max-h-80 overflow-y-auto">{{ $log }}</pre>
    </div>
    @endif

</div>
