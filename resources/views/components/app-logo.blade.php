@props([
    'sidebar' => false,
])

@if($sidebar)
    <a {{ $attributes->merge(['class' => 'flex items-center px-2 py-3']) }}>
        <img src="/dt360-black.png" alt="DeskTeam360" class="h-7 dark:hidden" />
        <img src="/dt360-white.png" alt="DeskTeam360" class="h-7 hidden dark:block" />
    </a>
@else
    <a {{ $attributes->merge(['class' => 'flex items-center']) }}>
        <img src="/dt360-black.png" alt="DeskTeam360" class="h-7 dark:hidden" />
        <img src="/dt360-white.png" alt="DeskTeam360" class="h-7 hidden dark:block" />
    </a>
@endif
