<x-layouts::app :title="__('API Builder — FD Archive')">
@php
    $label = 'text-xs font-semibold uppercase tracking-wide text-neutral-500 dark:text-neutral-400';
    $input = 'w-full rounded-lg border border-neutral-300 bg-white px-3 py-1.5 text-sm focus:outline-none focus:ring-2 focus:ring-purple-500 dark:border-neutral-600 dark:bg-neutral-800 dark:text-neutral-100';
    $btn   = 'rounded-lg border border-neutral-300 px-3 py-1.5 text-xs font-medium hover:bg-neutral-100 dark:border-neutral-600 dark:hover:bg-neutral-700';
@endphp
<div class="flex flex-col gap-6 p-6 lg:flex-row">

    {{-- FILTERS --}}
    <form id="builder-form" class="w-full shrink-0 space-y-3 lg:w-72" onsubmit="return false">
        <div class="flex items-center justify-between">
            <span class="text-sm font-semibold">Query</span>
            <button type="button" id="btn-reset" class="text-xs text-red-500 hover:text-red-700">Reset</button>
        </div>

        <div class="space-y-1">
            <div class="{{ $label }}">Single ticket ID</div>
            <input name="ticket_id" type="number" min="1" placeholder="Leave empty to search" class="{{ $input }}" />
        </div>

        <div id="list-filters" class="space-y-3">
            <div class="space-y-1">
                <div class="{{ $label }}">Search</div>
                <input name="search" type="text" placeholder="Subject, description, comments" class="{{ $input }}" />
            </div>

            <div class="space-y-1">
                <div class="{{ $label }}">Ticket IDs</div>
                <input name="ids" type="text" placeholder="110301, 110664" class="{{ $input }}" />
            </div>

            <div class="space-y-1">
                <div class="{{ $label }}">Status</div>
                <select name="statuses" multiple class="ts w-full">
                    @foreach($statusMap as $val => $text)<option value="{{ $val }}">{{ $text }}</option>@endforeach
                </select>
            </div>

            <div class="space-y-1">
                <div class="{{ $label }}">Priority</div>
                <select name="priorities" multiple class="ts w-full">
                    @foreach($priorityMap as $val => $text)<option value="{{ $val }}">{{ $text }}</option>@endforeach
                </select>
            </div>

            <div class="space-y-1">
                <div class="{{ $label }}">Type</div>
                <select name="types" multiple class="ts w-full">
                    @foreach($typeOptions as $type)<option value="{{ $type }}">{{ $type }}</option>@endforeach
                </select>
            </div>

            <div class="space-y-1">
                <div class="{{ $label }}">Source</div>
                <select name="sources" multiple class="ts w-full">
                    @foreach($sourceMap as $val => $text)<option value="{{ $val }}">{{ $text }}</option>@endforeach
                </select>
            </div>

            <div class="space-y-1">
                <div class="{{ $label }}">Company</div>
                <select name="companies" multiple class="ts w-full">
                    @foreach($companiesList as $c)<option value="{{ $c->fd_id }}">{{ $c->name }}</option>@endforeach
                </select>
            </div>

            <div class="space-y-1">
                <div class="{{ $label }}">Agent</div>
                <select name="agents" multiple class="ts w-full">
                    @foreach($agents as $a)<option value="{{ $a->fd_id }}">{{ $a->name }}</option>@endforeach
                </select>
            </div>

            <div class="space-y-1">
                <div class="{{ $label }}">Requester IDs</div>
                <input name="requesters" type="text" placeholder="Freshdesk contact IDs" class="{{ $input }}" />
            </div>

            <div class="space-y-1">
                <div class="{{ $label }}">Tags</div>
                <select name="tags" multiple class="ts w-full">
                    @foreach($allTags as $tag)<option value="{{ $tag }}">{{ $tag }}</option>@endforeach
                </select>
            </div>

            <div class="grid grid-cols-2 gap-2">
                <div class="space-y-1">
                    <div class="{{ $label }}">Created from</div>
                    <input name="created_from" type="date" class="{{ $input }}" />
                </div>
                <div class="space-y-1">
                    <div class="{{ $label }}">Created to</div>
                    <input name="created_to" type="date" class="{{ $input }}" />
                </div>
                <div class="space-y-1">
                    <div class="{{ $label }}">Updated from</div>
                    <input name="updated_from" type="date" class="{{ $input }}" />
                </div>
                <div class="space-y-1">
                    <div class="{{ $label }}">Updated to</div>
                    <input name="updated_to" type="date" class="{{ $input }}" />
                </div>
            </div>

            <div class="grid grid-cols-2 gap-2">
                <div class="space-y-1">
                    <div class="{{ $label }}">Sort</div>
                    <select name="sort" class="{{ $input }}">
                        <option value="">created_at</option>
                        <option value="updated_at">updated_at</option>
                        <option value="id">id</option>
                        <option value="status">status</option>
                        <option value="priority">priority</option>
                    </select>
                </div>
                <div class="space-y-1">
                    <div class="{{ $label }}">Order</div>
                    <select name="order" class="{{ $input }}">
                        <option value="">desc</option>
                        <option value="asc">asc</option>
                    </select>
                </div>
                <div class="space-y-1">
                    <div class="{{ $label }}">Per page</div>
                    <input name="per_page" type="number" min="1" max="100" placeholder="25" class="{{ $input }}" />
                </div>
                <div class="space-y-1">
                    <div class="{{ $label }}">Page</div>
                    <input name="page" type="number" min="1" placeholder="1" class="{{ $input }}" />
                </div>
            </div>

            <label class="flex items-center gap-2 text-sm">
                <input name="include" type="checkbox" value="comments" class="rounded border-neutral-300" />
                Include comments &amp; attachments
            </label>
        </div>
    </form>

    {{-- OUTPUT --}}
    <div class="min-w-0 flex-1 space-y-4">
        <div class="flex flex-wrap items-center justify-between gap-3">
            <div>
                <h1 class="text-lg font-semibold">API Builder</h1>
                <p class="text-sm text-neutral-500 dark:text-neutral-400">Build a request for the FD Archive API, preview the JSON, then copy it for a person or an AI agent.</p>
            </div>
            <div class="flex gap-2">
                <a href="{{ route('api-builder.docs') }}" target="_blank" class="{{ $btn }}">View docs (MD)</a>
                <a href="{{ route('api-builder.docs', ['download' => 1]) }}" class="{{ $btn }}">Download docs</a>
            </div>
        </div>

        @unless($keyConfigured)
            <div class="rounded-lg border border-amber-300 bg-amber-50 px-4 py-3 text-sm text-amber-800 dark:border-amber-700 dark:bg-amber-950 dark:text-amber-200">
                <strong>ARCHIVE_API_KEY</strong> is not set in <code>.env</code>, so the API answers 503 to everyone. The preview below still works because it uses your login.
            </div>
        @endunless

        <div class="space-y-1">
            <div class="flex items-center justify-between">
                <div class="{{ $label }}">Request URL</div>
                <button type="button" class="{{ $btn }}" data-copy="out-url">Copy</button>
            </div>
            <pre id="out-url" class="overflow-x-auto whitespace-pre-wrap break-all rounded-lg bg-neutral-100 p-3 text-xs dark:bg-neutral-800"></pre>
        </div>

        <div class="space-y-1">
            <div class="flex items-center justify-between">
                <div class="{{ $label }}">cURL</div>
                <button type="button" class="{{ $btn }}" data-copy="out-curl">Copy</button>
            </div>
            <pre id="out-curl" class="overflow-x-auto whitespace-pre-wrap break-all rounded-lg bg-neutral-100 p-3 text-xs dark:bg-neutral-800"></pre>
            <p class="text-xs text-neutral-500 dark:text-neutral-400">Send the key in the <code>X-API-Key</code> header. It is never shown on this page.</p>
        </div>

        <div class="space-y-1">
            <div class="flex items-center justify-between gap-2">
                <div class="{{ $label }}">Response <span id="out-meta" class="ml-2 normal-case font-normal"></span></div>
                <div class="flex gap-2">
                    <button type="button" class="{{ $btn }}" data-copy="out-json">Copy JSON</button>
                    <button type="button" id="btn-run" class="rounded-lg bg-purple-600 px-4 py-1.5 text-xs font-semibold text-white hover:bg-purple-700">Run</button>
                </div>
            </div>
            <pre id="out-json" class="max-h-[60vh] overflow-auto rounded-lg bg-neutral-900 p-3 text-xs text-neutral-100">Press Run to preview the response.</pre>
        </div>
    </div>
</div>

<script>
    document.addEventListener('DOMContentLoaded', function () {
        var API_BASE = @json($apiBase);
        var PREVIEW = @json(route('api-builder.preview'));
        var form = document.getElementById('builder-form');
        var selects = [];

        form.querySelectorAll('select.ts').forEach(function (el) {
            if (window.TomSelect) {
                selects.push(new TomSelect(el, { plugins: ['remove_button', 'checkbox_options'], maxOptions: 500, onChange: render }));
            }
        });

        function values() {
            var out = [];
            var ticketId = form.elements['ticket_id'].value.trim();
            if (ticketId) return { ticketId: ticketId, pairs: [] };
            Array.prototype.forEach.call(form.elements, function (el) {
                if (!el.name || el.name === 'ticket_id') return;
                var v;
                if (el.tagName === 'SELECT' && el.multiple) {
                    v = Array.prototype.filter.call(el.options, function (o) { return o.selected; }).map(function (o) { return o.value; }).join(',');
                } else if (el.type === 'checkbox') {
                    v = el.checked ? el.value : '';
                } else {
                    v = el.value.trim().replace(/\s*,\s*/g, ',');
                }
                if (v) out.push([el.name, v]);
            });
            return { ticketId: null, pairs: out };
        }

        function query(pairs) {
            // Commas stay readable: the API accepts comma lists for multi-value filters.
            return pairs.map(function (p) { return encodeURIComponent(p[0]) + '=' + encodeURIComponent(p[1]).replace(/%2C/g, ','); }).join('&');
        }

        function render() {
            var v = values();
            document.getElementById('list-filters').style.opacity = v.ticketId ? '0.4' : '1';
            var qs = query(v.pairs);
            var url = v.ticketId ? API_BASE + '/tickets/' + v.ticketId : API_BASE + '/tickets' + (qs ? '?' + qs : '');
            document.getElementById('out-url').textContent = 'GET ' + url;
            document.getElementById('out-curl').textContent = 'curl -H "X-API-Key: $ARCHIVE_API_KEY" \\\n  "' + url + '"';
        }

        function run() {
            var v = values();
            var qs = v.ticketId ? 'ticket_id=' + encodeURIComponent(v.ticketId) : query(v.pairs);
            var out = document.getElementById('out-json');
            var meta = document.getElementById('out-meta');
            out.textContent = 'Loading…';
            meta.textContent = '';
            var started = Date.now();
            fetch(PREVIEW + (qs ? '?' + qs : ''), { headers: { Accept: 'application/json' }, credentials: 'same-origin' })
                .then(function (r) { return r.text().then(function (t) { return { status: r.status, text: t }; }); })
                .then(function (r) {
                    var body;
                    try { body = JSON.parse(r.text); } catch (e) { body = null; }
                    out.textContent = body ? JSON.stringify(body, null, 2) : r.text.slice(0, 5000);
                    var parts = ['HTTP ' + r.status, (Date.now() - started) + ' ms'];
                    if (body && body.meta) parts.push(body.meta.total + ' tickets, page ' + body.meta.page + ' of ' + body.meta.last_page);
                    meta.textContent = parts.join(' · ');
                })
                .catch(function (e) { out.textContent = 'Request failed: ' + e.message; });
        }

        form.addEventListener('input', render);
        form.addEventListener('change', render);
        form.addEventListener('keydown', function (e) { if (e.key === 'Enter' && e.target.tagName === 'INPUT') run(); });
        document.getElementById('btn-run').addEventListener('click', run);
        document.getElementById('btn-reset').addEventListener('click', function () {
            form.reset();
            selects.forEach(function (s) { s.clear(true); });
            render();
        });
        document.querySelectorAll('[data-copy]').forEach(function (b) {
            b.addEventListener('click', function () {
                var text = document.getElementById(b.dataset.copy).textContent.replace(/^GET /, '');
                navigator.clipboard.writeText(text).then(function () {
                    var old = b.textContent;
                    b.textContent = 'Copied';
                    setTimeout(function () { b.textContent = old; }, 1200);
                });
            });
        });

        render();
    });
</script>
</x-layouts::app>
