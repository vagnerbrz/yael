@props([
    'title' => '',
    'icon' => null,
])

<div {{ $attributes->class('rounded-2xl border border-zinc-200 bg-white/80 p-5 dark:border-zinc-700 dark:bg-zinc-800') }}>
    <div class="mb-4 flex items-center gap-2">
        @if ($icon)
            <flux:icon :icon="$icon" class="size-5 text-zinc-500" />
        @endif
        <flux:heading size="lg">{{ $title }}</flux:heading>
    </div>

    {{ $slot }}
</div>
