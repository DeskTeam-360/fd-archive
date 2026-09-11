<meta charset="utf-8" />
<meta name="viewport" content="width=device-width, initial-scale=1.0" />

<title>
    {{ filled($title ?? null) ? $title.' - '.config('app.name', 'Laravel') : config('app.name', 'Laravel') }}
</title>

<link rel="icon" href="/favicon.ico" sizes="any">
<link rel="icon" href="/favicon.svg" type="image/svg+xml">
<link rel="apple-touch-icon" href="/apple-touch-icon.png">

@fonts

@vite(['resources/css/app.css', 'resources/js/app.js'])
@fluxAppearance

<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/tom-select/2.3.1/css/tom-select.min.css" />
<style>
    .ts-wrapper { min-height: 32px; }
    .ts-control { border-radius: 0.5rem !important; border-color: rgb(212 212 212) !important; background: white !important; font-size: 0.8rem !important; padding: 4px 8px !important; min-height: 32px; }
    .ts-dropdown { font-size: 0.8rem !important; border-radius: 0.5rem !important; }
    .ts-control .item { background: rgb(237 233 254) !important; color: rgb(109 40 217) !important; border-radius: 4px !important; font-size: 0.7rem !important; padding: 1px 6px !important; }
    .ts-control .item .remove { color: rgb(109 40 217) !important; }
</style>
