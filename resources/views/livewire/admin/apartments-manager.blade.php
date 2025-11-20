<div class="space-y-6">
        <div>
            <flux:heading size="xl">{{ __('Gestão de apartamentos') }}</flux:heading>
            <flux:subheading>{{ __('Cadastre, edite ou remova apartamentos e organize o condomínio.') }}</flux:subheading>
        </div>

        @if (session('status'))
            <flux:callout variant="success" icon="check-circle" :text="session('status')" />
        @endif

        <div class="grid gap-6 lg:grid-cols-3">
            <div class="rounded-2xl border border-zinc-200 bg-white/80 p-5 shadow-sm dark:border-zinc-700 dark:bg-zinc-800">
                <flux:heading size="lg">
                    {{ $editingId ? __('Editar apartamento') : __('Novo apartamento') }}
                </flux:heading>

                <form wire:submit.prevent="save" class="mt-4 space-y-4">
                    <label class="text-sm font-semibold text-zinc-600 dark:text-zinc-200">
                        {{ __('Bloco') }}
                        <select
                            wire:model="form.block_id"
                            class="mt-1 w-full rounded-xl border border-zinc-300 bg-white/80 px-3 py-2 text-sm shadow-sm focus:border-indigo-500 focus:outline-none focus:ring-2 focus:ring-indigo-400/30 dark:border-zinc-600 dark:bg-zinc-800"
                        >
                            <option value="">{{ __('Selecione') }}</option>
                            @foreach ($blocks as $block)
                                <option value="{{ $block->id }}">{{ $block->name }}</option>
                            @endforeach
                        </select>
                    </label>

                    <div class="grid gap-4 sm:grid-cols-2">
                        <flux:input
                            wire:model="form.number"
                            label="{{ __('Número') }}"
                            placeholder="101"
                            maxlength="10"
                        />

                        <label class="text-sm font-semibold text-zinc-600 dark:text-zinc-200">
                            {{ __('Lado') }}
                            <select
                                wire:model="form.side"
                                class="mt-1 w-full rounded-xl border border-zinc-300 bg-white/80 px-3 py-2 text-sm shadow-sm focus:border-indigo-500 focus:outline-none focus:ring-2 focus:ring-indigo-400/30 dark:border-zinc-600 dark:bg-zinc-800"
                            >
                                <option value="A">{{ __('Lado A') }}</option>
                                <option value="B">{{ __('Lado B') }}</option>
                            </select>
                        </label>
                    </div>

                    <label class="text-sm font-semibold text-zinc-600 dark:text-zinc-200">
                        {{ __('Status') }}
                        <select
                            wire:model="form.status"
                            class="mt-1 w-full rounded-xl border border-zinc-300 bg-white/80 px-3 py-2 text-sm shadow-sm focus:border-indigo-500 focus:outline-none focus:ring-2 focus:ring-indigo-400/30 dark:border-zinc-600 dark:bg-zinc-800"
                        >
                            <option value="ocupado">{{ __('Ocupado') }}</option>
                            <option value="vago">{{ __('Vago') }}</option>
                        </select>
                    </label>

                    <flux:textarea
                        wire:model="form.notes"
                        label="{{ __('Observações') }}"
                        rows="3"
                    ></flux:textarea>

                    <div class="flex items-center gap-3">
                        <flux:button type="submit" variant="primary" class="flex-1">
                            {{ $editingId ? __('Atualizar') : __('Salvar') }}
                        </flux:button>

                        @if ($editingId)
                            <flux:button type="button" variant="ghost" wire:click="cancelEdit">
                                {{ __('Cancelar') }}
                            </flux:button>
                        @endif
                    </div>
                </form>
            </div>

            <div class="lg:col-span-2 space-y-4">
                <div class="rounded-2xl border border-zinc-200 bg-white/80 p-4 dark:border-zinc-700 dark:bg-zinc-800">
                    <div class="grid gap-4 md:grid-cols-4">
                        <flux:input wire:model.live.debounce.500ms="search" label="{{ __('Buscar') }}" placeholder="101, Bloco 1..." />

                        <label class="text-sm font-semibold text-zinc-600 dark:text-zinc-200">
                            {{ __('Bloco') }}
                            <select
                                wire:model.live="blockFilter"
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
                                wire:model.live="sideFilter"
                                class="mt-1 w-full rounded-xl border border-zinc-300 bg-white/80 px-3 py-2 text-sm shadow-sm focus:border-indigo-500 focus:outline-none focus:ring-2 focus:ring-indigo-400/30 dark:border-zinc-600 dark:bg-zinc-800"
                            >
                                <option value="">{{ __('Todos') }}</option>
                                <option value="A">{{ __('Lado A') }}</option>
                                <option value="B">{{ __('Lado B') }}</option>
                            </select>
                        </label>

                        <label class="text-sm font-semibold text-zinc-600 dark:text-zinc-200">
                            {{ __('Status') }}
                            <select
                                wire:model.live="statusFilter"
                                class="mt-1 w-full rounded-xl border border-zinc-300 bg-white/80 px-3 py-2 text-sm shadow-sm focus:border-indigo-500 focus:outline-none focus:ring-2 focus:ring-indigo-400/30 dark:border-zinc-600 dark:bg-zinc-800"
                            >
                                <option value="">{{ __('Todos') }}</option>
                                <option value="ocupado">{{ __('Ocupado') }}</option>
                                <option value="vago">{{ __('Vago') }}</option>
                            </select>
                        </label>
                    </div>
                </div>

                <div class="overflow-hidden rounded-2xl border border-zinc-200 bg-white/80 dark:border-zinc-700 dark:bg-zinc-800">
                    <table class="min-w-full divide-y divide-zinc-200 text-sm dark:divide-zinc-700">
                        <thead class="bg-zinc-50/50 dark:bg-zinc-800">
                            <tr>
                                <th class="px-4 py-3 text-left font-semibold">{{ __('Bloco') }}</th>
                                <th class="px-4 py-3 text-left font-semibold">{{ __('Apartamento') }}</th>
                                <th class="px-4 py-3 text-left font-semibold">{{ __('Status') }}</th>
                                <th class="px-4 py-3 text-left font-semibold">{{ __('Ações') }}</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-zinc-200 dark:divide-zinc-700">
                            @forelse ($apartments as $apartment)
                                <tr>
                                    <td class="px-4 py-4">
                                        <p class="font-semibold text-zinc-900 dark:text-white">{{ $apartment->block->name }}{{ __(':side', ['side' => $apartment->side]) }}</p>
                                        {{-- <p class="text-xs text-zinc-500">{{ __('Lado :side', ['side' => $apartment->side]) }}</p> --}}
                                    </td>
                                    <td class="px-4 py-4">
                                        <p class="font-semibold">{{ $apartment->number }}</p>
                                        @if ($apartment->notes)
                                            <p class="text-xs text-zinc-500">{{ $apartment->notes }}</p>
                                        @endif
                                    </td>
                                    <td class="px-4 py-4">
                                        <flux:badge :color="$apartment->status === 'ocupado' ? 'green' : 'zinc'">
                                            {{ $apartment->status === 'ocupado' ? __('Ocupado') : __('Vago') }}
                                        </flux:badge>
                                    </td>
                                    <td class="px-4 py-4">
                                        <div class="flex flex-wrap gap-2">
                                            <flux:button size="xs" variant="ghost" wire:click="edit({{ $apartment->id }})">
                                                {{ __('Editar') }}
                                            </flux:button>
                                            <flux:button
                                                size="xs"
                                                variant="danger"
                                                x-data
                                                x-on:click.prevent="if (confirm('{{ __('Tem certeza que deseja remover este apartamento?') }}')) { $wire.delete({{ $apartment->id }}) }"
                                            >
                                                {{ __('Remover') }}
                                            </flux:button>
                                        </div>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="4" class="px-4 py-6 text-center text-sm text-zinc-500">
                                        {{ __('Nenhum apartamento encontrado para os filtros selecionados.') }}
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>

                    <div class="px-4 py-3">
                        {{ $apartments->links() }}
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
