@props([
    'label' => '',
    'primary' => '',
    'secondary' => '',
])

<div class="rounded-2xl border border-zinc-200 bg-white/80 p-5 shadow-sm dark:border-zinc-700 dark:bg-zinc-800">
    <p class="text-xs font-semibold uppercase tracking-wide text-zinc-500">{{ $label }}</p>
    <p class="mt-2 text-3xl font-bold text-zinc-900 dark:text-white">{{ $primary }}</p>
    @if ($secondary)
        <p class="mt-1 text-xs text-zinc-500">{{ $secondary }}</p>
    @endif
</div>
