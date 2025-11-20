<?php

namespace App\Livewire;

use App\Models\Apartment;
use App\Models\Correspondence;
use App\Models\WaterCharge;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Livewire\Component;

class Dashboard extends Component
{
    public string $competence;

    protected $rules = [
        'competence' => 'required|date_format:Y-m',
    ];

    public function mount(): void
    {
        $this->competence = Carbon::now()->format('Y-m');
    }

    public function updatedCompetence(): void
    {
        $this->validateOnly('competence');
    }

    public function render()
    {
        $user = Auth::user();

        $payload = $user->isAdmin()
            ? $this->adminPayload()
            : $this->residentPayload();

        return view('livewire.dashboard', array_merge([
            'competence' => $this->competence,
            'user' => $user,
        ], $payload))->layout('components.layouts.app', [
            'title' => __('Dashboard'),
        ]);
    }

    protected function adminPayload(): array
    {
        $charges = WaterCharge::where('competence', $this->competence);

        return [
            'stats' => [
                'apartments_total' => Apartment::count(),
                'apartments_occupied' => Apartment::where('status', 'ocupado')->count(),
                'apartments_vacant' => Apartment::where('status', 'vago')->count(),
                'charges_open' => (clone $charges)->where('status', 'aberto')->count(),
                'charges_paid' => (clone $charges)->where('status', 'pago')->count(),
                'charges_amount_paid' => (clone $charges)->where('status', 'pago')->sum('amount'),
                'charges_amount_open' => (clone $charges)->whereIn('status', ['aberto', 'atrasado'])->sum('amount'),
                'pending_correspondences' => Correspondence::where('status', 'pendente')->count(),
            ],
            'recentPayments' => WaterCharge::with('apartment.block')
                ->where('status', 'pago')
                ->latest('paid_at')
                ->limit(6)
                ->get(),
            'pendingCorrespondences' => Correspondence::with('apartment.block')
                ->where('status', 'pendente')
                ->latest('received_at')
                ->limit(6)
                ->get(),
            'monthlyTrend' => WaterCharge::select('competence', DB::raw('COUNT(*) as quantity'))
                ->groupBy('competence')
                ->orderByDesc('competence')
                ->limit(6)
                ->get()
                ->reverse()
                ->values(),
        ];
    }

    protected function residentPayload(): array
    {
        $user = Auth::user();
        $apartmentIds = $user->apartments()->pluck('apartments.id');

        $chargesQuery = WaterCharge::whereIn('apartment_id', $apartmentIds);

        return [
            'openCharges' => (clone $chargesQuery)->where('status', 'aberto')->orderBy('due_date')->get(),
            'recentCharges' => (clone $chargesQuery)->latest('due_date')->limit(8)->get(),
            'pendingCorrespondences' => Correspondence::with('apartment.block')
                ->whereIn('apartment_id', $apartmentIds)
                ->where('status', 'pendente')
                ->latest('received_at')
                ->get(),
        ];
    }
}
