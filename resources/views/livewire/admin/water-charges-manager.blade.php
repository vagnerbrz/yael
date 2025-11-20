<div class="space-y-6">
        <div class="flex flex-col gap-3 md:flex-row md:items-end">
            <div>
                <flux:heading size="xl">{{ __('Cobranças de água') }}</flux:heading>
                <flux:subheading>{{ __('Gere lançamentos mensais e acompanhe pagamentos.') }}</flux:subheading>
            </div>

            <div class="flex flex-col gap-3 md:ms-auto md:flex-row md:items-end">
                <flux:input
                    type="month"
                    wire:model.live="competence"
                    label="{{ __('Competência') }}"
                    min="2024-01"
                />
                <flux:button wire:click="generateCharges" variant="primary">
                    {{ __('Gerar cobranças do mês') }}
                </flux:button>
            </div>
        </div>

        @if (session('status'))
            <flux:callout variant="success" icon="check-circle" :text="session('status')" />
        @endif

        <div class="grid gap-4 sm:grid-cols-2 lg:grid-cols-4">
            <x-dashboard.stat-card
                :label="__('Total do mes')"
                :primary="__('R$ :value', ['value' => number_format($stats['total_amount'], 2, ',', '.')])"
                :secondary="__('lançamentos totais: :count', ['count' => $stats['open'] + $stats['paid'] + $stats['late']])"
            />
            <x-dashboard.stat-card
                :label="__('Em aberto')"
                :primary="$stats['open']"
                :secondary="__('Cobranças aguardando pagamento')"
            />
            <x-dashboard.stat-card
                :label="__('Pagas')"
                :primary="$stats['paid']"
                :secondary="__('Confirmadas pela administração')"
            />
            <x-dashboard.stat-card
                :label="__('Em atraso')"
                :primary="$stats['late']"
                :secondary="__('Com vencimento ultrapassado')"
            />
        </div>

        <div class="rounded-2xl border border-zinc-200 bg-white/80 p-4 dark:border-zinc-700 dark:bg-zinc-800">
            <div class="grid gap-4 md:grid-cols-4">
                <label class="text-sm font-semibold text-zinc-600 dark:text-zinc-200">
                    {{ __('Status') }}
                    <select
                        wire:model.live="statusFilter"
                        class="mt-1 w-full rounded-xl border border-zinc-300 bg-white/80 px-3 py-2 text-sm shadow-sm focus:border-indigo-500 focus:outline-none focus:ring-2 focus:ring-indigo-400/30 dark:border-zinc-600 dark:bg-zinc-800"
                    >
                        <option value="">{{ __('Todos') }}</option>
                        <option value="aberto">{{ __('Em aberto') }}</option>
                        <option value="pago">{{ __('Pago') }}</option>
                        <option value="atrasado">{{ __('Em atraso') }}</option>
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
                        <th class="px-4 py-3 text-left font-semibold">{{ __('Competência') }}</th>
                        <th class="px-4 py-3 text-left font-semibold">{{ __('Vencimento') }}</th>
                        <th class="px-4 py-3 text-left font-semibold">{{ __('Valor') }}</th>
                        <th class="px-4 py-3 text-left font-semibold">{{ __('Status') }}</th>
                        <th class="px-4 py-3 text-left font-semibold">{{ __('Ações') }}</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-zinc-200 dark:divide-zinc-700">
                    @forelse ($charges as $charge)
                        <tr>
                            <td class="px-4 py-4">
                                <p class="font-semibold text-zinc-900 dark:text-white">{{ $charge->apartment->block->name }}{{ $charge->apartment->side}} - {{ $charge->apartment->number}}</p>
                                <p class="text-xs text-zinc-500">{{ $charge->apartment->block->name }}</p>
                            </td>
                            <td class="px-4 py-4">{{ $charge->competence }}</td>
                            <td class="px-4 py-4">{{ $charge->due_date->format('d/m/Y') }}</td>
                            <td class="px-4 py-4">{{ __('R$ :value', ['value' => number_format($charge->amount, 2, ',', '.')]) }}</td>
                            <td class="px-4 py-4">
                                <flux:badge :color="$charge->status === 'pago' ? 'green' : ($charge->status === 'atrasado' ? 'red' : 'zinc')">
                                    {{ strtoupper($charge->status) }}
                                </flux:badge>
                            </td>
                            <td class="px-4 py-4">
                                <flux:button
                                    size="sm"
                                    wire:click="toggleStatus({{ $charge->id }})"
                                    :variant="$charge->status === 'pago' ? 'ghost' : 'primary'"
                                >
                                    {{ $charge->status === 'pago' ? __('Reabrir') : __('Marcar como pago') }}
                                </flux:button>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="px-4 py-6 text-center text-sm text-zinc-500">
                                {{ __('Nenhuma cobrança encontrada para os filtros selecionados.') }}
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>

            <div class="px-4 py-3">
                {{ $charges->links() }}
            </div>
        </div>
    </div>
</div>
