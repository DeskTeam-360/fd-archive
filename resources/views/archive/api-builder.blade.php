<x-layouts::app :title="__('API Builder — FD Archive')">
<style>
    .apib { --bd:#d4d4d4; --bg:#fff; --fg:#171717; --muted:#737373; --panel:#f5f5f5; --accent:#9333ea;
        display:flex; gap:24px; padding:24px; align-items:flex-start; color:var(--fg); }
    .dark .apib { --bd:#404040; --bg:#262626; --fg:#f5f5f5; --muted:#a3a3a3; --panel:#262626; }
    .apib-form { width:300px; flex:0 0 300px; display:flex; flex-direction:column; gap:12px; }
    .apib-out { flex:1 1 0; min-width:0; display:flex; flex-direction:column; gap:16px; }
    .apib-row { display:flex; align-items:center; justify-content:space-between; gap:12px; flex-wrap:wrap; }
    .apib-field { display:flex; flex-direction:column; gap:4px; min-width:0; }
    .apib-grid { display:grid; grid-template-columns:1fr 1fr; gap:8px; }
    .apib-label { font-size:11px; font-weight:600; letter-spacing:.05em; text-transform:uppercase; color:var(--muted); }
    .apib-input { width:100%; box-sizing:border-box; border:1px solid var(--bd); background:var(--bg); color:var(--fg);
        border-radius:8px; padding:6px 10px; font-size:13px; }
    .apib-input:focus { outline:2px solid var(--accent); outline-offset:-1px; }
    .apib-btn { border:1px solid var(--bd); background:transparent; color:var(--fg); border-radius:8px; padding:5px 12px;
        font-size:12px; font-weight:500; cursor:pointer; text-decoration:none; white-space:nowrap; }
    .apib-btn:hover { background:var(--panel); }
    .apib-run { background:var(--accent); border-color:var(--accent); color:#fff; font-weight:600; padding:5px 18px; }
    .apib-run:hover { background:#7e22ce; }
    .apib-reset { background:none; border:0; color:#ef4444; font-size:12px; cursor:pointer; }
    .apib-title { font-size:18px; font-weight:600; margin:0; }
    .apib-sub { font-size:13px; color:var(--muted); margin:2px 0 0; }
    .apib-hint { font-size:12px; color:var(--muted); margin:0; }
    .apib-pre { margin:0; padding:12px; border-radius:8px; background:var(--panel); border:1px solid var(--bd); color:var(--fg);
        font-size:12px; line-height:1.5; white-space:pre-wrap; word-break:break-all; overflow-x:auto; }
    .apib-json { background:#171717; color:#f5f5f5; white-space:pre; word-break:normal; overflow:auto; max-height:60vh; }
    .apib-warn { border:1px solid #f59e0b; background:rgba(245,158,11,.12); border-radius:8px; padding:10px 14px; font-size:13px; }
    .apib-check { display:flex; align-items:center; gap:8px; font-size:13px; }
    .apib .ts-wrapper { width:100%; }
    .apib .ts-control, .apib .ts-control input { background:var(--bg) !important; color:var(--fg) !important; }
    .apib .ts-control { border:1px solid var(--bd) !important; border-radius:8px; min-height:32px; padding:4px 8px; font-size:13px; box-shadow:none; }
    .apib .ts-dropdown { background:var(--bg); color:var(--fg); border:1px solid var(--bd); font-size:13px; }
    .apib .ts-dropdown .option:hover, .apib .ts-dropdown .active { background:var(--panel); color:var(--fg); }
    .apib .ts-control .item { background:var(--panel) !important; color:var(--fg) !important; border:1px solid var(--bd) !important; border-radius:6px; }
    @media (max-width: 900px) { .apib { flex-direction:column; } .apib-form { width:100%; flex-basis:auto; } }
</style>
<div class="apib">

    {{-- FILTERS --}}
    <form id="builder-form" class="apib-form" onsubmit="return false">
        <div class="apib-row">
            <span style="font-size:14px;font-weight:600">Query</span>
            <button type="button" id="btn-reset" class="apib-reset">Reset</button>
        </div>

        <div class="apib-field">
            <div class="apib-label">Single ticket ID</div>
            <input name="ticket_id" type="number" min="1" placeholder="Leave empty to search" class="apib-input" />
        </div>

        <div id="list-filters" style="display:flex;flex-direction:column;gap:12px">
            <div class="apib-field">
                <div class="apib-label">Search</div>
                <input name="search" type="text" placeholder="Subject, description, comments" class="apib-input" />
            </div>

            <div class="apib-field">
                <div class="apib-label">Ticket IDs</div>
                <input name="ids" type="text" placeholder="110301, 110664" class="apib-input" />
            </div>

            <div class="apib-field">
                <div class="apib-label">Status</div>
                <select name="statuses" multiple class="ts">
                    @foreach($statusMap as $val => $text)<option value="{{ $val }}">{{ $text }}</option>@endforeach
                </select>
            </div>

            <div class="apib-field">
                <div class="apib-label">Priority</div>
                <select name="priorities" multiple class="ts">
                    @foreach($priorityMap as $val => $text)<option value="{{ $val }}">{{ $text }}</option>@endforeach
                </select>
            </div>

            <div class="apib-field">
                <div class="apib-label">Type</div>
                <select name="types" multiple class="ts">
                    @foreach($typeOptions as $type)<option value="{{ $type }}">{{ $type }}</option>@endforeach
                </select>
            </div>

            <div class="apib-field">
                <div class="apib-label">Source</div>
                <select name="sources" multiple class="ts">
                    @foreach($sourceMap as $val => $text)<option value="{{ $val }}">{{ $text }}</option>@endforeach
                </select>
            </div>

            <div class="apib-field">
                <div class="apib-label">Company</div>
                <select name="companies" multiple class="ts">
                    @foreach($companiesList as $c)<option value="{{ $c->fd_id }}">{{ $c->name }}</option>@endforeach
                </select>
            </div>

            <div class="apib-field">
                <div class="apib-label">Agent</div>
                <select name="agents" multiple class="ts">
                    @foreach($agents as $a)<option value="{{ $a->fd_id }}">{{ $a->name }}</option>@endforeach
                </select>
            </div>

            <div class="apib-field">
                <div class="apib-label">Requester IDs</div>
                <input name="requesters" type="text" placeholder="Freshdesk contact IDs" class="apib-input" />
            </div>

            <div class="apib-field">
                <div class="apib-label">Tags</div>
                <select name="tags" multiple class="ts">
                    @foreach($allTags as $tag)<option value="{{ $tag }}">{{ $tag }}</option>@endforeach
                </select>
            </div>

            <div class="apib-grid">
                <div class="apib-field">
                    <div class="apib-label">Created from</div>
                    <input name="created_from" type="date" class="apib-input" />
                </div>
                <div class="apib-field">
                    <div class="apib-label">Created to</div>
                    <input name="created_to" type="date" class="apib-input" />
                </div>
                <div class="apib-field">
                    <div class="apib-label">Updated from</div>
                    <input name="updated_from" type="date" class="apib-input" />
                </div>
                <div class="apib-field">
                    <div class="apib-label">Updated to</div>
                    <input name="updated_to" type="date" class="apib-input" />
                </div>
            </div>

            <div class="apib-grid">
                <div class="apib-field">
                    <div class="apib-label">Sort</div>
                    <select name="sort" class="apib-input">
                        <option value="">created_at</option>
                        <option value="updated_at">updated_at</option>
                        <option value="id">id</option>
                        <option value="status">status</option>
                        <option value="priority">priority</option>
                    </select>
                </div>
                <div class="apib-field">
                    <div class="apib-label">Order</div>
                    <select name="order" class="apib-input">
                        <option value="">desc</option>
                        <option value="asc">asc</option>
                    </select>
                </div>
                <div class="apib-field">
                    <div class="apib-label">Per page</div>
                    <input name="per_page" type="number" min="1" max="100" placeholder="25" class="apib-input" />
                </div>
                <div class="apib-field">
                    <div class="apib-label">Page</div>
                    <input name="page" type="number" min="1" placeholder="1" class="apib-input" />
                </div>
            </div>

            <label class="apib-check">
                <input name="include" type="checkbox" value="comments" />
                Include comments &amp; attachments
            </label>
        </div>
    </form>

    {{-- OUTPUT --}}
    <div class="apib-out">
        <div class="apib-row">
            <div>
                <h1 class="apib-title">API Builder</h1>
                <p class="apib-sub">Build a request for the FD Archive API, preview the JSON, then copy it for a person or an AI agent.</p>
            </div>
            <div style="display:flex;gap:8px">
                <a href="{{ route('api-builder.docs') }}" target="_blank" class="apib-btn">View docs (MD)</a>
                <a href="{{ route('api-builder.docs', ['download' => 1]) }}" class="apib-btn">Download docs</a>
            </div>
        </div>

        @unless($keyConfigured)
            <div class="apib-warn">
                <strong>ARCHIVE_API_KEY</strong> is not set in <code>.env</code>, so the API answers 503 to everyone. The preview below still works because it uses your login.
            </div>
        @endunless

        <div class="apib-field">
            <div class="apib-row">
                <div class="apib-label">Request URL</div>
                <button type="button" class="apib-btn" data-copy="out-url">Copy</button>
            </div>
            <pre id="out-url" class="apib-pre"></pre>
        </div>

        <div class="apib-field">
            <div class="apib-row">
                <div class="apib-label">cURL</div>
                <button type="button" class="apib-btn" data-copy="out-curl">Copy</button>
            </div>
            <pre id="out-curl" class="apib-pre"></pre>
            <p class="apib-hint">Send the key in the <code>X-API-Key</code> header. It is never shown on this page.</p>
        </div>

        <div class="apib-field">
            <div class="apib-row">
                <div class="apib-label">Response <span id="out-meta" style="margin-left:8px;text-transform:none;font-weight:400;letter-spacing:0"></span></div>
                <div style="display:flex;gap:8px">
                    <button type="button" class="apib-btn" data-copy="out-json">Copy JSON</button>
                    <button type="button" id="btn-run" class="apib-btn apib-run">Run</button>
                </div>
            </div>
            <pre id="out-json" class="apib-pre apib-json">Press Run to preview the response.</pre>
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
