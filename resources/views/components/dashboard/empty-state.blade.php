@props([
    'visible' => false,
    'message' => '',
])

@if ($visible)
    <div class="rounded-xl border border-dashed border-zinc-300 bg-white/60 p-4 text-center text-sm text-zinc-500 dark:border-zinc-600 dark:bg-transparent">
        {{ $message }}
    </div>
@else
    {{ $slot }}
@endif
