<?php

namespace App\Livewire\Admin;

use App\Models\Correspondence;
use App\Models\WaterCharge;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Auth;
use Livewire\Component;
use Symfony\Component\HttpFoundation\StreamedResponse;

class Reports extends Component
{
    public string $chargesCompetence;

    public function mount(): void
    {
        abort_unless(Auth::user()?->isAdmin(), 403);

        $this->chargesCompetence = Carbon::now()->format('Y-m');
    }

    public function updatedChargesCompetence(): void
    {
        $this->validate([
            'chargesCompetence' => 'required|date_format:Y-m',
        ]);
    }

    public function export(string $type): StreamedResponse
    {
        abort_unless(Auth::user()?->isAdmin(), 403);

        return match ($type) {
            'charges' => $this->exportCharges(),
            'correspondences' => $this->exportCorrespondences(),
            default => abort(404),
        };
    }

    public function render()
    {
        $chargesSummary = WaterCharge::selectRaw('status, COUNT(*) as total')
            ->where('competence', $this->chargesCompetence)
            ->groupBy('status')
            ->pluck('total', 'status')
            ->toArray();

        $correspondenceSummary = Correspondence::selectRaw('status, COUNT(*) as total')
            ->groupBy('status')
            ->pluck('total', 'status')
            ->toArray();

        return view('livewire.admin.reports', [
            'chargesSummary' => $chargesSummary,
            'correspondenceSummary' => $correspondenceSummary,
        ])->layout('components.layouts.app', [
            'title' => __('Relatórios e exportações'),
        ]);
    }

    protected function exportCharges(): StreamedResponse
    {
        $fileName = "cobrancas-{$this->chargesCompetence}.csv";
        $competence = $this->chargesCompetence;

        return response()->streamDownload(function () use ($competence) {
            $handle = fopen('php://output', 'w');
            fputcsv($handle, ['Apartamento', 'Competencia', 'Valor', 'Vencimento', 'Status', 'Pago em'], ';');

            WaterCharge::with('apartment.block')
                ->where('competence', $competence)
                ->orderBy('due_date')
                ->chunk(200, function ($charges) use ($handle) {
                    foreach ($charges as $charge) {
                        fputcsv($handle, [
                            $charge->apartment->display_name,
                            $charge->competence,
                            number_format($charge->amount, 2, ',', '.'),
                            optional($charge->due_date)->format('d/m/Y'),
                            strtoupper($charge->status),
                            optional($charge->paid_at)?->format('d/m/Y H:i'),
                        ], ';');
                    }
                });

            fclose($handle);
        }, $fileName);
    }

    protected function exportCorrespondences(): StreamedResponse
    {
        $fileName = 'correspondencias.csv';

        return response()->streamDownload(function () {
            $handle = fopen('php://output', 'w');
            fputcsv($handle, ['Apartamento', 'Tipo', 'Transportadora', 'Recebido em', 'Status', 'Retirado em', 'Retirado por'], ';');

            Correspondence::with('apartment.block')
                ->orderByDesc('received_at')
                ->chunk(200, function ($items) use ($handle) {
                    foreach ($items as $item) {
                        fputcsv($handle, [
                            $item->apartment->display_name,
                            $item->type,
                            $item->carrier,
                            optional($item->received_at)?->format('d/m/Y H:i'),
                            strtoupper($item->status),
                            optional($item->retrieved_at)?->format('d/m/Y H:i'),
                            $item->retrieved_by_name,
                        ], ';');
                    }
                });

            fclose($handle);
        }, $fileName);
    }
}
