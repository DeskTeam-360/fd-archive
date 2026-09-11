<x-layouts.archive title="FD Archive" :wide="true">
    <x-slot name="styles">
        <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/tom-select/2.3.1/css/tom-select.min.css" />
        <style>
            .ts-wrapper { min-height: 36px; }
            .ts-control { border-radius: 0.5rem !important; border-color: rgb(212 212 212) !important; background: white !important; font-size: 0.8rem !important; padding: 4px 8px !important; min-height: 36px; }
            .dark .ts-control { border-color: rgb(82 82 82) !important; background: rgb(38 38 38) !important; color: white !important; }
            .ts-dropdown { font-size: 0.8rem !important; border-radius: 0.5rem !important; }
            .ts-control .item { background: rgb(237 233 254) !important; color: rgb(109 40 217) !important; border-radius: 4px !important; font-size: 0.7rem !important; padding: 1px 6px !important; }
            .ts-control .item .remove { color: rgb(109 40 217) !important; }
        </style>
    </x-slot>

    <livewire:archive.archive-search />

    <x-slot name="scripts">
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
                succeed(() => { requestAnimationFrame(() => { if (!_tsSyncing) initAllTomSelects(); }); });
            });
        </script>
    </x-slot>
</x-layouts.archive>
