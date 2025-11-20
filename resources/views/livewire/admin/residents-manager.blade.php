<div class="space-y-6">
        <div>
            <flux:heading size="xl">{{ __('Moradores e apartamentos') }}</flux:heading>
            <flux:subheading>{{ __('Cadastre moradores e vincule apartamentos ocupados.') }}</flux:subheading>
        </div>

        @if (session('status'))
            <flux:callout variant="success" icon="check-circle" :text="session('status')" />
        @endif

        <div class="grid gap-6 lg:grid-cols-3">
            <div class="rounded-2xl border border-zinc-200 bg-white/80 p-5 shadow-sm dark:border-zinc-700 dark:bg-zinc-800">
                <flux:heading size="lg">{{ __('Novo morador') }}</flux:heading>

                <form wire:submit.prevent="saveResident" class="mt-4 space-y-4">
                    <flux:input wire:model="form.name" label="{{ __('Nome completo') }}" required />
                    <flux:input wire:model="form.email" label="{{ __('E-mail') }}" type="email" required />
                    <flux:input wire:model="form.document" label="{{ __('CPF / Documento') }}" />
                    <flux:input wire:model="form.phone" label="{{ __('Telefone') }}" />

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
                                        <option value="{{ $apartment->id }}">
                                            {{ $apartment->display_name }} ({{ $apartment->status === 'ocupado' ? __('ocupado') : __('vago') }})
                                        </option>
                                    @endforeach
                                </optgroup>
                            @endforeach
                        </select>
                    </label>

                    <label class="text-sm font-semibold text-zinc-600 dark:text-zinc-200">
                        {{ __('Tipo de responsável') }}
                        <select
                            wire:model="form.responsibility_type"
                            class="mt-1 w-full rounded-xl border border-zinc-300 bg-white/80 px-3 py-2 text-sm shadow-sm focus:border-indigo-500 focus:outline-none focus:ring-2 focus:ring-indigo-400/30 dark:border-zinc-600 dark:bg-zinc-800"
                        >
                            <option value="proprietario">{{ __('Proprietário') }}</option>
                            <option value="inquilino">{{ __('Inquilino') }}</option>
                        </select>
                    </label>

                    <label class="inline-flex items-center gap-2 text-sm text-zinc-600 dark:text-zinc-200">
                        <input type="checkbox" wire:model="form.is_primary" class="rounded border-zinc-300 text-indigo-600 focus:ring-indigo-500" />
                        {{ __('Morador principal do apartamento') }}
                    </label>

                    <flux:button type="submit" class="w-full" variant="primary">{{ __('Salvar') }}</flux:button>
                </form>
            </div>

            <div class="lg:col-span-2 space-y-4">
                <div class="rounded-2xl border border-zinc-200 bg-white/80 p-4 dark:border-zinc-700 dark:bg-zinc-800">
                    <div class="grid gap-4 md:grid-cols-3">
                        <flux:input wire:model.live.debounce.500ms="search" label="{{ __('Buscar') }}" placeholder="{{ __('Nome ou número') }}" />

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
                                <th class="px-4 py-3 text-left font-semibold">{{ __('Moradores') }}</th>
                                <th class="px-4 py-3 text-left font-semibold">{{ __('Status') }}</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-zinc-200 dark:divide-zinc-700">
                            @foreach ($apartments as $apartment)
                                <tr>
                                    <td class="px-4 py-4">
                                        <p class="font-semibold text-zinc-900 dark:text-white">{{ $apartment->block->name }}{{ __(':side', ['side' => $apartment->side]) }} - {{ $apartment->number }}</p>
                                        <p class="text-xs text-zinc-500">{{ $apartment->block->name }}</p>
                                    </td>
                                    <td class="px-4 py-4">
                                        @forelse ($apartment->residents as $resident)
                                            <div class="mb-2 flex items-center justify-between gap-2 rounded-xl border border-zinc-200 px-3 py-2 dark:border-zinc-600">
                                                <div>
                                                    <p class="text-sm font-semibold">{{ $resident->name }}</p>
                                                    <p class="text-xs text-zinc-500">
                                                        {{ $resident->pivot->responsibility_type === 'proprietario' ? __('Proprietário') : __('Inquilino') }}
                                                        @if ($resident->pivot->is_primary)
                                                            · {{ __('Principal') }}
                                                        @endif
                                                    </p>
                                                </div>
                                                <flux:button
                                                    size="xs"
                                                    variant="ghost"
                                                    wire:click="detachResident({{ $apartment->id }}, {{ $resident->id }})"
                                                >
                                                    {{ __('Remover') }}
                                                </flux:button>
                                            </div>
                                        @empty
                                            <p class="text-xs text-zinc-500">{{ __('Sem moradores cadastrados') }}</p>
                                        @endforelse
                                    </td>
                                    <td class="px-4 py-4">
                                        <flux:badge :color="$apartment->status === 'ocupado' ? 'green' : 'zinc'">
                                            {{ $apartment->status === 'ocupado' ? __('Ocupado') : __('Vago') }}
                                        </flux:badge>
                                    </td>
                                </tr>
                            @endforeach
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
