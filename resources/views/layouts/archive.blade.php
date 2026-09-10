<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>{{ $title ?? 'FD Archive' }}</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    @livewireStyles
</head>
<body class="bg-neutral-100 dark:bg-neutral-900 text-neutral-900 dark:text-neutral-100 min-h-screen">
    <nav class="bg-white dark:bg-neutral-800 border-b border-neutral-200 dark:border-neutral-700 px-6 py-3 flex items-center gap-6 text-sm">
        <span class="font-bold text-base">📦 FD Archive</span>
        <a href="{{ route('archive') }}" class="text-neutral-500 hover:text-neutral-900 dark:hover:text-white {{ request()->routeIs('archive') ? 'text-purple-600 font-medium' : '' }}">Search</a>
        <a href="{{ route('archive') }}?tab=companies" class="text-neutral-500 hover:text-neutral-900 dark:hover:text-white">Companies</a>
        <a href="{{ route('archive') }}?tab=contacts" class="text-neutral-500 hover:text-neutral-900 dark:hover:text-white">Contacts</a>
        <a href="{{ route('archive') }}?tab=tickets" class="text-neutral-500 hover:text-neutral-900 dark:hover:text-white">Tickets</a>
        <div class="ml-auto">
            <a href="{{ route('freshdesk-import') }}" class="text-xs text-neutral-400 hover:text-neutral-600">→ Import</a>
        </div>
    </nav>
    <div class="max-w-6xl mx-auto py-6 px-4">
        {{ $slot }}
    </div>
    @livewireScripts
</body>
</html>
