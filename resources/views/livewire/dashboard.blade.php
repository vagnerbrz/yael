<div class="space-y-6">
        <div class="flex flex-col gap-3 md:flex-row md:items-end">
            <div>
                <flux:heading size="xl">
                    {{ $user->isAdmin() ? __('Visão geral do condomínio') : __('Bem-vindo de volta') }}
                </flux:heading>
                <flux:subheading>
                    {{ $user->isAdmin()
                        ? __('Acompanhe rapidamente taxas de água e correspondências.')
                        : __('Veja suas pendências de água e avisos de correspondência.') }}
                </flux:subheading>
            </div>

            <div class="md:ms-auto w-full max-w-xs">
                <flux:input
                    type="month"
                    label="{{ __('Competência') }}"
                    wire:model.live="competence"
                    min="2024-01"
                />
            </div>
        </div>

        @if ($user->isAdmin())
            <div class="grid gap-4 sm:grid-cols-2 xl:grid-cols-4">
                <x-dashboard.stat-card
                    :label="__('Apês ocupados / total')"
                    :primary="number_format($stats['apartments_occupied'])"
                    :secondary="__('de :total', ['total' => number_format($stats['apartments_total'])])"
                />
                <x-dashboard.stat-card
                    :label="__('Apês vagos')"
                    :primary="number_format($stats['apartments_vacant'])"
                    :secondary="__('disponíveis para cadastro')"
                />
                <x-dashboard.stat-card
                    :label="__('Cobranças em aberto')"
                    :primary="number_format($stats['charges_open'])"
                    :secondary="__('R$ :value em aberto', [
                        'value' => number_format($stats['charges_amount_open'], 2, ',', '.'),
                    ])"
                />
                <x-dashboard.stat-card
                    :label="__('Cobranças pagas')"
                    :primary="number_format($stats['charges_paid'])"
                    :secondary="__('R$ :value recebidos', [
                        'value' => number_format($stats['charges_amount_paid'], 2, ',', '.'),
                    ])"
                />
                <x-dashboard.stat-card
                    :label="__('Correspondências pendentes')"
                    :primary="number_format($stats['pending_correspondences'])"
                    :secondary="__('aguardando retirada')"
                />
            </div>

            <div class="grid gap-6 lg:grid-cols-2">
                <x-dashboard.panel :title="__('Últimos pagamentos confirmados')" icon="banknotes">
                    <x-dashboard.empty-state
                        :visible="$recentPayments->isEmpty()"
                        :message="__('Nenhum pagamento confirmado ainda.')"
                    >
                        <ul class="divide-y divide-zinc-200 dark:divide-zinc-700">
                            @foreach ($recentPayments as $payment)
                                <li class="py-3 flex items-center justify-between">
                                    <div>
                                        <p class="text-sm font-medium text-zinc-900 dark:text-white">
                                            {{ $payment->apartment->display_name }}
                                            – {{ __('R$ :value', ['value' => number_format($payment->amount, 2, ',', '.')]) }}
                                        </p>
                                        <p class="text-xs text-zinc-500">
                                            {{ __('Pago em :date', ['date' => optional($payment->paid_at)?->format('d/m/Y')]) }}
                                        </p>
                                    </div>
                                    <flux:badge>{{ strtoupper($payment->status) }}</flux:badge>
                                </li>
                            @endforeach
                        </ul>
                    </x-dashboard.empty-state>
                </x-dashboard.panel>

                <x-dashboard.panel :title="__('Correspondências aguardando retirada')" icon="inbox">
                    <x-dashboard.empty-state
                        :visible="$pendingCorrespondences->isEmpty()"
                        :message="__('Sem itens pendentes.')"
                    >
                        <ul class="divide-y divide-zinc-200 dark:divide-zinc-700">
                            @foreach ($pendingCorrespondences as $item)
                                <li class="py-3">
                                    <p class="text-sm font-semibold text-zinc-900 dark:text-white">
                                        {{ $item->apartment->display_name }} – {{ $item->type }}
                                    </p>
                                    <p class="text-xs text-zinc-500">
                                        {{ __('Recebido em :date por :carrier', [
                                            'date' => $item->received_at->format('d/m/Y H:i'),
                                            'carrier' => $item->carrier ?: '---',
                                        ]) }}
                                    </p>
                                </li>
                            @endforeach
                        </ul>
                    </x-dashboard.empty-state>
                </x-dashboard.panel>
            </div>

            <x-dashboard.panel :title="__('Histórico recente de competências')" icon="chart-bar">
                <div class="flex flex-wrap gap-4">
                    @foreach ($monthlyTrend as $record)
                        <div class="flex flex-col rounded-lg border border-dashed border-zinc-300 p-3 dark:border-zinc-600">
                            <span class="text-xs uppercase text-zinc-500">{{ $record->competence }}</span>
                            <span class="text-lg font-semibold">{{ $record->quantity }} {{ __('lançamentos') }}</span>
                        </div>
                    @endforeach
                </div>
            </x-dashboard.panel>
        @else
            <div class="grid gap-6 lg:grid-cols-2">
                <x-dashboard.panel :title="__('Cobranças em aberto')" icon="exclamation-triangle">
                    <x-dashboard.empty-state
                        :visible="$openCharges->isEmpty()"
                        :message="__('Você está em dia!')"
                    >
                        <ul class="divide-y divide-zinc-200 dark:divide-zinc-700">
                            @foreach ($openCharges as $charge)
                                <li class="py-4 flex items-center justify-between">
                                    <div>
                                        <p class="text-sm font-medium">
                                            {{ $charge->apartment->display_name }} – {{ __('Competência :competence', ['competence' => $charge->competence]) }}
                                        </p>
                                        <p class="text-xs text-zinc-500">
                                            {{ __('Vence em :due', ['due' => $charge->due_date->format('d/m/Y')]) }}
                                        </p>
                                    </div>
                                    <flux:badge color="red">{{ __('Em aberto') }}</flux:badge>
                                </li>
                            @endforeach
                        </ul>
                    </x-dashboard.empty-state>
                </x-dashboard.panel>

                <x-dashboard.panel :title="__('Avisos de correspondência')" icon="inbox-stack">
                    <x-dashboard.empty-state
                        :visible="$pendingCorrespondences->isEmpty()"
                        :message="__('Nenhuma correspondência aguardando retirada.')"
                    >
                        <ul class="divide-y divide-zinc-200 dark:divide-zinc-700">
                            @foreach ($pendingCorrespondences as $item)
                                <li class="py-4">
                                    <p class="text-sm font-medium">
                                        {{ $item->apartment->display_name }} – {{ $item->type }}
                                    </p>
                                    <p class="text-xs text-zinc-500">
                                        {{ __('Recebido em :date', ['date' => $item->received_at->format('d/m/Y H:i')]) }}
                                    </p>
                                </li>
                            @endforeach
                        </ul>
                    </x-dashboard.empty-state>
                </x-dashboard.panel>
            </div>

            <x-dashboard.panel :title="__('Histórico recente de cobranças')" icon="clock">
                <div class="overflow-hidden rounded-xl border border-zinc-200 dark:border-zinc-700">
                    <table class="min-w-full divide-y divide-zinc-200 dark:divide-zinc-700 text-sm">
                        <thead class="bg-zinc-50/50 dark:bg-zinc-800">
                            <tr>
                                <th class="px-4 py-3 text-left font-semibold">{{ __('Apartamento') }}</th>
                                <th class="px-4 py-3 text-left font-semibold">{{ __('Competência') }}</th>
                                <th class="px-4 py-3 text-left font-semibold">{{ __('Valor') }}</th>
                                <th class="px-4 py-3 text-left font-semibold">{{ __('Status') }}</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-zinc-200 dark:divide-zinc-700">
                            @foreach ($recentCharges as $charge)
                                <tr>
                                    <td class="px-4 py-3">{{ $charge->apartment->display_name }}</td>
                                    <td class="px-4 py-3">{{ $charge->competence }}</td>
                                    <td class="px-4 py-3">{{ __('R$ :value', ['value' => number_format($charge->amount, 2, ',', '.')]) }}</td>
                                    <td class="px-4 py-3">
                                        <flux:badge :color="$charge->status === 'pago' ? 'green' : 'zinc'">
                                            {{ strtoupper($charge->status) }}
                                        </flux:badge>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </x-dashboard.panel>
        @endif
    </div>
</div>
