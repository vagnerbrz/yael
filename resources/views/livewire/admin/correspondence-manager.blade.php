<div class="space-y-6">
        <div>
            <flux:heading size="xl">{{ __('Correios e encomendas') }}</flux:heading>
            <flux:subheading>{{ __('Registre entradas e confirme retiradas com poucos cliques.') }}</flux:subheading>
        </div>

        @if (session('status'))
            <flux:callout variant="success" icon="check-circle" :text="session('status')" />
        @endif

        <div class="grid gap-6 lg:grid-cols-3">
            <div class="rounded-2xl border border-zinc-200 bg-white/80 p-5 shadow-sm dark:border-zinc-700 dark:bg-zinc-800">
                <flux:heading size="lg">{{ __('Nova correspondência') }}</flux:heading>

                <form wire:submit.prevent="saveCorrespondence" class="mt-4 space-y-4">
                    <label class="text-sm font-semibold text-zinc-600 dark:text-zinc-200">
                        {{ __('Apartamento') }}
                        <select
                            wire:model="form.apartment_id"
                            class="mt-1 w-full rounded-xl border border-zinc-300 bg-white/80 px-3 py-2 text-sm shadow-sm focus:border-indigo-500 focus:outline-none focus:ring-2 focus:ring-indigo-400/30 dark:border-zinc-600 dark:bg-zinc-800"
                            required
                        >
                            <option value="">{{ __('Selecione') }}</option>
                            @foreach ($blocks as $block)
                                <optgroup label="{{ $block->name }}">
                                    @foreach ($block->apartments as $apartment)
                                        <option value="{{ $apartment->id }}">{{ $apartment->display_name }}</option>
                                    @endforeach
                                </optgroup>
                            @endforeach
                        </select>
                    </label>

                    <flux:input wire:model="form.type" label="{{ __('Tipo de encomenda') }}" required />
                    <flux:input wire:model="form.carrier" label="{{ __('Transportadora') }}" />
                    <flux:textarea wire:model="form.description" label="{{ __('Observações') }}" rows="3" />
                    <flux:input wire:model="form.received_at" label="{{ __('Data/hora de recebimento') }}" type="datetime-local" />

                    <flux:button type="submit" class="w-full" variant="primary">{{ __('Registrar') }}</flux:button>
                </form>
            </div>

            <div class="lg:col-span-2 space-y-4">
                <div class="rounded-2xl border border-zinc-200 bg-white/80 p-4 dark:border-zinc-700 dark:bg-zinc-800">
                    <div class="grid gap-4 md:grid-cols-4">
                        <flux:input wire:model.live.debounce.500ms="search" label="{{ __('Buscar') }}" placeholder="{{ __('Tipo ou número') }}" />

                        <label class="text-sm font-semibold text-zinc-600 dark:text-zinc-200">
                            {{ __('Status') }}
                            <select
                                wire:model.live="statusFilter"
                                class="mt-1 w-full rounded-xl border border-zinc-300 bg-white/80 px-3 py-2 text-sm shadow-sm focus:border-indigo-500 focus:outline-none focus:ring-2 focus:ring-indigo-400/30 dark:border-zinc-600 dark:bg-zinc-800"
                            >
                                <option value="pendente">{{ __('Aguardando retirada') }}</option>
                                <option value="retirado">{{ __('Retiradas') }}</option>
                            </select>
                        </label>

                        <label class="text-sm font-semibold text-zinc-600 dark:text-zinc-200">
                            {{ __('Bloco') }}
                            <select
                                wire:model.live="blockId"
                                class="mt-1 w-full rounded-xl border border-zinc-300 bg-white/80 px-3 py-2 text-sm shadow-sm focus:border-indigo-500 focus:outline-none focus:ring-2 focus:ring-indigo-400/30 dark:border-zinc-600 dark:bg-zinc-800"
                            >
                                <option value="">{{ __('Todos') }}</option>
                                @foreach ($blocks as $block)
                                    <option value="{{ $block->id }}">{{ $block->name }}</option>
                                @endforeach
                            </select>
                        </label>

                        <label class="text-sm font-semibold text-zinc-600 dark:text-zinc-200">
                            {{ __('Lado') }}
                            <select
                                wire:model.live="side"
                                class="mt-1 w-full rounded-xl border border-zinc-300 bg-white/80 px-3 py-2 text-sm shadow-sm focus:border-indigo-500 focus:outline-none focus:ring-2 focus:ring-indigo-400/30 dark:border-zinc-600 dark:bg-zinc-800"
                            >
                                <option value="">{{ __('Todos') }}</option>
                                <option value="A">{{ __('Lado A') }}</option>
                                <option value="B">{{ __('Lado B') }}</option>
                            </select>
                        </label>
                    </div>
                </div>

                <div class="overflow-hidden rounded-2xl border border-zinc-200 bg-white/80 dark:border-zinc-700 dark:bg-zinc-800">
                    <table class="min-w-full divide-y divide-zinc-200 text-sm dark:divide-zinc-700">
                        <thead class="bg-zinc-50/50 dark:bg-zinc-800">
                            <tr>
                                <th class="px-4 py-3 text-left font-semibold">{{ __('Apartamento') }}</th>
                                <th class="px-4 py-3 text-left font-semibold">{{ __('Tipo') }}</th>
                                <th class="px-4 py-3 text-left font-semibold">{{ __('Recebido em') }}</th>
                                <th class="px-4 py-3 text-left font-semibold">{{ __('Status') }}</th>
                                <th class="px-4 py-3 text-left font-semibold">{{ __('Ações') }}</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-zinc-200 dark:divide-zinc-700">
                            @forelse ($correspondences as $item)
                                <tr>
                                    <td class="px-4 py-4">
                                        <p class="font-semibold text-zinc-900 dark:text-white">{{ $item->apartment->block->name }}{{ $item->apartment->side }} - {{ $item->apartment->number }}</p>
                                        <p class="text-xs text-zinc-500">{{ $item->apartment->block->name }}</p>
                                    </td>
                                    <td class="px-4 py-4">
                                        <p class="font-semibold">{{ $item->type }}</p>
                                        <p class="text-xs text-zinc-500">{{ $item->carrier ?: __('Transportadora não informada') }}</p>
                                    </td>
                                    <td class="px-4 py-4">
                                        <p>{{ $item->received_at->format('d/m/Y H:i') }}</p>
                                        @if ($item->status === 'retirado' && $item->retrieved_at)
                                            <p class="text-xs text-zinc-500">
                                                {{ __('Retirada em :date', ['date' => $item->retrieved_at->format('d/m/Y H:i')]) }}
                                            </p>
                                        @endif
                                    </td>
                                    <td class="px-4 py-4">
                                        <flux:badge :color="$item->status === 'retirado' ? 'green' : 'zinc'">
                                            {{ $item->status === 'retirado' ? __('Retirada') : __('Aguardando') }}
                                        </flux:badge>
                                        @if ($item->retrieved_by_name)
                                            <p class="text-xs text-zinc-500 mt-1">{{ __('Por :name', ['name' => $item->retrieved_by_name]) }}</p>
                                        @endif
                                    </td>
                                    <td class="px-4 py-4">
                                        @if ($item->status === 'pendente')
                                            <flux:input
                                                wire:model="retrievalNames.{{ $item->id }}"
                                                placeholder="{{ __('Quem retirou?') }}"
                                            />
                                            <flux:button
                                                size="sm"
                                                class="mt-2"
                                                wire:click="markAsRetrieved({{ $item->id }})"
                                            >
                                                {{ __('Marcar como retirada') }}
                                            </flux:button>
                                        @else
                                            <p class="text-xs text-zinc-500">{{ __('Retirada confirmada') }}</p>
                                        @endif
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="5" class="px-4 py-6 text-center text-sm text-zinc-500">
                                        {{ __('Nenhuma correspondência encontrada para os filtros.') }}
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>

                    <div class="px-4 py-3">
                        {{ $correspondences->links() }}
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
