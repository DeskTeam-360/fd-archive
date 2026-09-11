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
    <nav class="bg-white dark:bg-neutral-800 border-b border-neutral-200 dark:border-neutral-700 px-6 py-3 flex items-center gap-1 text-sm">
        <span class="font-bold text-base mr-4">📦 FD Archive</span>

        @php
            $navLink = fn(string $label, string $route, array $params = []) =>
                '<a href="' . route($route, $params) . '" class="px-3 py-1.5 rounded-md transition-colors ' .
                (request()->routeIs($route) ? 'bg-purple-100 dark:bg-purple-900/40 text-purple-700 dark:text-purple-300 font-medium' : 'text-neutral-500 hover:bg-neutral-100 dark:hover:bg-neutral-700 hover:text-neutral-900 dark:hover:text-white') .
                '">' . $label . '</a>';
        @endphp

        {!! $navLink('Archive', 'archive') !!}
        {!! $navLink('Import', 'freshdesk-import') !!}
        {!! $navLink('Users', 'users.index') !!}

        <div class="ml-auto flex items-center gap-3">
            <span class="text-neutral-400 text-xs">{{ auth()->user()?->name }}</span>
            <form method="POST" action="{{ route('logout') }}">
                @csrf
                <button type="submit" class="text-xs text-neutral-400 hover:text-red-500 transition-colors">Logout</button>
            </form>
        </div>
    </nav>
    <div class="max-w-6xl mx-auto py-6 px-4">
        {{ $slot }}
    </div>
    @livewireScripts
</body>
</html>
