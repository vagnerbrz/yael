<div class="space-y-6">
        <div>
            <flux:heading size="xl">{{ __('Relatórios do condomínio') }}</flux:heading>
            <flux:subheading>{{ __('Exporte planilhas com cobranças e correspondências.') }}</flux:subheading>
        </div>

        <div class="grid gap-6 md:grid-cols-2">
            <div class="rounded-2xl border border-zinc-200 bg-white/80 p-5 dark:border-zinc-700 dark:bg-zinc-800">
                <div class="flex items-center justify-between gap-3">
                    <div>
                        <flux:heading size="lg">{{ __('Cobranças de água') }}</flux:heading>
                        <flux:subheading>{{ __('Resumo da competência selecionada') }}</flux:subheading>
                    </div>
                    <flux:button wire:click="export('charges')">
                        {{ __('Exportar CSV') }}
                    </flux:button>
                </div>

                <div class="mt-4 grid gap-4 sm:grid-cols-2">
                    <flux:input
                        type="month"
                        wire:model.live="chargesCompetence"
                        label="{{ __('Competência') }}"
                    />

                    <div class="rounded-2xl border border-zinc-200 p-4 text-sm dark:border-zinc-700">
                        <p class="font-semibold text-zinc-900 dark:text-white">{{ __('Status das cobranças') }}</p>
                        <ul class="mt-2 space-y-1">
                            <li>{{ __('Em aberto: :value', ['value' => $chargesSummary['aberto'] ?? 0]) }}</li>
                            <li>{{ __('Pagas: :value', ['value' => $chargesSummary['pago'] ?? 0]) }}</li>
                            <li>{{ __('Em atraso: :value', ['value' => $chargesSummary['atrasado'] ?? 0]) }}</li>
                        </ul>
                    </div>
                </div>
            </div>

            <div class="rounded-2xl border border-zinc-200 bg-white/80 p-5 dark:border-zinc-700 dark:bg-zinc-800">
                <div class="flex items-center justify-between gap-3">
                    <div>
                        <flux:heading size="lg">{{ __('Correspondências') }}</flux:heading>
                        <flux:subheading>{{ __('Exportação completa do período') }}</flux:subheading>
                    </div>
                    <flux:button wire:click="export('correspondences')">
                        {{ __('Exportar CSV') }}
                    </flux:button>
                </div>

                <div class="mt-4 rounded-2xl border border-zinc-200 p-4 text-sm dark:border-zinc-700">
                    <p class="font-semibold text-zinc-900 dark:text-white">{{ __('Status das correspondências') }}</p>
                    <ul class="mt-2 space-y-1">
                        <li>{{ __('Aguardando retirada: :value', ['value' => $correspondenceSummary['pendente'] ?? 0]) }}</li>
                        <li>{{ __('Retiradas: :value', ['value' => $correspondenceSummary['retirado'] ?? 0]) }}</li>
                    </ul>
                    <p class="mt-4 text-xs text-zinc-500">
                        {{ __('O arquivo inclui histórico completo com datas e responsáveis pela retirada.') }}
                    </p>
                </div>
            </div>
        </div>
    </div>
</div>
