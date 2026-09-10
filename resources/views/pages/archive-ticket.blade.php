<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>Ticket — FD Archive</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    @livewireStyles
</head>
<body class="bg-neutral-100 dark:bg-neutral-900 text-neutral-900 dark:text-neutral-100 min-h-screen">
    <nav class="bg-white dark:bg-neutral-800 border-b border-neutral-200 dark:border-neutral-700 px-6 py-3 flex items-center gap-6 text-sm sticky top-0 z-10">
        <a href="/archive" class="font-bold text-base text-neutral-700 dark:text-neutral-200 hover:text-purple-600">📦 FD Archive</a>
        <span class="text-neutral-400">/</span>
        <span class="text-purple-600 font-medium">Ticket</span>
        <div class="ml-auto">
            <a href="/import" class="text-xs text-neutral-400 hover:text-neutral-600">→ Import</a>
        </div>
    </nav>
    <div class="max-w-4xl mx-auto py-6 px-4">
        <livewire:archive.archive-ticket :ticket-id="(int) $id" />
    </div>
    @livewireScripts
</body>
</html>
