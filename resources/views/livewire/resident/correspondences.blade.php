<div class="space-y-6">
        <div>
            <flux:heading size="xl">{{ __('Correspondências do meu apartamento') }}</flux:heading>
            <flux:subheading>{{ __('Confira pendências e histórico de retiradas.') }}</flux:subheading>
        </div>

        @if (session('status'))
            <flux:callout variant="success" icon="check-circle" :text="session('status')" />
        @endif

        <div class="rounded-2xl border border-zinc-200 bg-white/80 p-4 dark:border-zinc-700 dark:bg-zinc-800">
            <label class="text-sm font-semibold text-zinc-600 dark:text-zinc-200">
                {{ __('Apartamento') }}
                <select
                    wire:model.live="apartmentId"
                    class="mt-1 w-full rounded-xl border border-zinc-300 bg-white/80 px-3 py-2 text-sm shadow-sm focus:border-indigo-500 focus:outline-none focus:ring-2 focus:ring-indigo-400/30 dark:border-zinc-600 dark:bg-zinc-800"
                >
                    <option value="">{{ __('Todos') }}</option>
                    @foreach ($apartments as $apartment)
                        <option value="{{ $apartment->id }}">{{ $apartment->display_name }}</option>
                    @endforeach
                </select>
            </label>
        </div>

        <x-dashboard.panel :title="__('Pendentes de retirada')" icon="inbox-stack">
            <x-dashboard.empty-state
                :visible="$pending->isEmpty()"
                :message="__('Nenhuma correspondência aguardando.')"
            >
                <ul class="divide-y divide-zinc-200 dark:divide-zinc-700">
                    @foreach ($pending as $item)
                        <li class="py-4 flex flex-col gap-2 md:flex-row md:items-center md:justify-between">
                            <div>
                                <p class="font-semibold text-zinc-900 dark:text-white">
                                    {{ $item->apartment->display_name }} – {{ $item->type }}
                                </p>
                                <p class="text-xs text-zinc-500">
                                    {{ __('Recebido em :date', ['date' => $item->received_at->format('d/m/Y H:i')]) }}
                                </p>
                            </div>
                            <flux:button size="sm" wire:click="confirmPickup({{ $item->id }})">
                                {{ __('Confirmar retirada') }}
                            </flux:button>
                        </li>
                    @endforeach
                </ul>
            </x-dashboard.empty-state>
        </x-dashboard.panel>

        <x-dashboard.panel :title="__('Histórico de retiradas')" icon="archive-box">
            <div class="overflow-hidden rounded-xl border border-zinc-200 dark:border-zinc-700">
                <table class="min-w-full divide-y divide-zinc-200 text-sm dark:divide-zinc-700">
                    <thead class="bg-zinc-50/50 dark:bg-zinc-800">
                        <tr>
                            <th class="px-4 py-3 text-left font-semibold">{{ __('Apartamento') }}</th>
                            <th class="px-4 py-3 text-left font-semibold">{{ __('Tipo') }}</th>
                            <th class="px-4 py-3 text-left font-semibold">{{ __('Recebido em') }}</th>
                            <th class="px-4 py-3 text-left font-semibold">{{ __('Retirado em') }}</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-zinc-200 dark:divide-zinc-700">
                        @forelse ($history as $item)
                            <tr>
                                <td class="px-4 py-3">{{ $item->apartment->display_name }}</td>
                                <td class="px-4 py-3">{{ $item->type }}</td>
                                <td class="px-4 py-3">{{ $item->received_at->format('d/m/Y H:i') }}</td>
                                <td class="px-4 py-3">
                                    {{ optional($item->retrieved_at)->format('d/m/Y H:i') }}
                                    @if ($item->retrieved_by_name)
                                        <p class="text-xs text-zinc-500">{{ __('Por :name', ['name' => $item->retrieved_by_name]) }}</p>
                                    @endif
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="4" class="px-4 py-6 text-center text-sm text-zinc-500">
                                    {{ __('Nenhum histórico de retirada.') }}
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <div class="px-4 py-3">
                {{ $history->links() }}
            </div>
        </x-dashboard.panel>
    </div>
</div>
