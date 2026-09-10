<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>FD Archive</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    @livewireStyles
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/tom-select/2.3.1/css/tom-select.min.css" />
    <style>
        .ts-wrapper { min-height: 36px; }
        .ts-control { border-radius: 0.5rem !important; border-color: rgb(212 212 212) !important; background: white !important; font-size: 0.8rem !important; padding: 4px 8px !important; min-height: 36px; }
        .dark .ts-control { border-color: rgb(82 82 82) !important; background: rgb(38 38 38) !important; color: white !important; }
        .ts-dropdown { font-size: 0.8rem !important; border-radius: 0.5rem !important; }
        .ts-control .item { background: rgb(237 233 254) !important; color: rgb(109 40 217) !important; border-radius: 4px !important; font-size: 0.7rem !important; padding: 1px 6px !important; }
        .ts-control .item .remove { color: rgb(109 40 217) !important; }
    </style>
</head>
<body class="bg-neutral-100 dark:bg-neutral-900 text-neutral-900 dark:text-neutral-100 min-h-screen">
    <nav class="bg-white dark:bg-neutral-800 border-b border-neutral-200 dark:border-neutral-700 px-6 py-3 flex items-center gap-6 text-sm sticky top-0 z-10">
        <span class="font-bold text-base">📦 FD Archive</span>
        <a href="/archive" class="text-purple-600 font-medium">Search</a>
        <div class="ml-auto">
            <a href="/import" class="text-xs text-neutral-400 hover:text-neutral-600">→ Import</a>
        </div>
    </nav>
    <div class="max-w-7xl mx-auto py-6 px-4">
        <livewire:archive.archive-search />
    </div>
    @livewireScripts
    <script src="https://cdnjs.cloudflare.com/ajax/libs/tom-select/2.3.1/js/tom-select.complete.min.js"></script>
    <script>
        let _tsSyncing = false;

        function initTomSelect(el, wireProperty, component) {
            if (el._tomSelect) { el._tomSelect.destroy(); el._tomSelect = null; }
            const ts = new TomSelect(el, {
                plugins: ['remove_button', 'checkbox_options'],
                maxOptions: 300,
                onItemAdd()    { if (!_tsSyncing) syncTS(ts, wireProperty, component); },
                onItemRemove() { if (!_tsSyncing) syncTS(ts, wireProperty, component); },
            });
            el._tomSelect = ts;
        }

        function syncTS(ts, prop, component) {
            _tsSyncing = true;
            component.$set(prop, ts.getValue());
            setTimeout(() => { _tsSyncing = false; }, 500);
        }

        function initAllTomSelects() {
            if (_tsSyncing) return;
            document.querySelectorAll('[data-ts-prop]').forEach(el => {
                const prop = el.dataset.tsProp;
                const comp = Livewire.find(el.closest('[wire\\:id]')?.getAttribute('wire:id'));
                if (comp) initTomSelect(el, prop, comp);
            });
        }

        document.addEventListener('livewire:initialized', initAllTomSelects);

        Livewire.hook('commit', ({ succeed }) => {
            succeed(() => {
                requestAnimationFrame(() => {
                    if (!_tsSyncing) initAllTomSelects();
                });
            });
        });
    </script>
</body>
</html>
