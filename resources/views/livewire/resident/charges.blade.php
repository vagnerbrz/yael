<div class="space-y-6">
        <div>
            <flux:heading size="xl">{{ __('Minhas cobranças') }}</flux:heading>
            <flux:subheading>{{ __('Consulte o histórico e verifique pendências.') }}</flux:subheading>
        </div>

        <div class="grid gap-4 sm:grid-cols-3">
            <x-dashboard.stat-card :label="__('Em aberto')" :primary="$stats['open']" />
            <x-dashboard.stat-card :label="__('Pagas')" :primary="$stats['paid']" />
            <x-dashboard.stat-card :label="__('Em atraso')" :primary="$stats['late']" />
        </div>

        <div class="rounded-2xl border border-zinc-200 bg-white/80 p-4 dark:border-zinc-700 dark:bg-zinc-800">
            <div class="grid gap-4 md:grid-cols-3">
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

                <label class="text-sm font-semibold text-zinc-600 dark:text-zinc-200">
                    {{ __('Status') }}
                    <select
                        wire:model.live="statusFilter"
                        class="mt-1 w-full rounded-xl border border-zinc-300 bg-white/80 px-3 py-2 text-sm shadow-sm focus:border-indigo-500 focus:outline-none focus:ring-2 focus:ring-indigo-400/30 dark:border-zinc-600 dark:bg-zinc-800"
                    >
                        <option value="">{{ __('Todos') }}</option>
                        <option value="aberto">{{ __('Em aberto') }}</option>
                        <option value="pago">{{ __('Pagas') }}</option>
                        <option value="atrasado">{{ __('Em atraso') }}</option>
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
                    </tr>
                </thead>
                <tbody class="divide-y divide-zinc-200 dark:divide-zinc-700">
                    @forelse ($charges as $charge)
                        <tr>
                            <td class="px-4 py-4">{{ $charge->apartment->display_name }}</td>
                            <td class="px-4 py-4">{{ $charge->competence }}</td>
                            <td class="px-4 py-4">{{ $charge->due_date->format('d/m/Y') }}</td>
                            <td class="px-4 py-4">{{ __('R$ :value', ['value' => number_format($charge->amount, 2, ',', '.')]) }}</td>
                            <td class="px-4 py-4">
                                <flux:badge :color="$charge->status === 'pago' ? 'green' : ($charge->status === 'atrasado' ? 'red' : 'zinc')">
                                    {{ strtoupper($charge->status) }}
                                </flux:badge>
                                @if ($charge->status === 'pago' && $charge->paid_at)
                                    <p class="text-xs text-zinc-500 mt-1">{{ __('Registrado em :date', ['date' => $charge->paid_at->format('d/m/Y')]) }}</p>
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="px-4 py-6 text-center text-sm text-zinc-500">
                                {{ __('Nenhuma cobrança encontrada.') }}
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
