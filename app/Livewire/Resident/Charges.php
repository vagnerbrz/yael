<?php

namespace App\Livewire\Resident;

use App\Models\WaterCharge;
use Illuminate\Support\Facades\Auth;
use Livewire\Component;
use Livewire\WithPagination;

class Charges extends Component
{
    use WithPagination;

    public string $apartmentId = '';
    public string $statusFilter = '';

    public function mount(): void
    {
        abort_unless(Auth::check(), 403);

        $firstApartment = Auth::user()->apartments()->first();
        $this->apartmentId = $firstApartment?->id ? (string) $firstApartment->id : '';
    }

    public function updatingApartmentId(): void
    {
        $this->resetPage();
    }

    public function updatingStatusFilter(): void
    {
        $this->resetPage();
    }

    public function render()
    {
        $user = Auth::user();
        $apartmentIds = $user->apartments()->pluck('apartments.id');

        $query = WaterCharge::with('apartment.block')
            ->whereIn('apartment_id', $apartmentIds)
            ->when($this->apartmentId !== '', fn ($builder) => $builder->where('apartment_id', $this->apartmentId))
            ->when($this->statusFilter !== '', fn ($builder) => $builder->where('status', $this->statusFilter))
            ->orderByDesc('due_date');

        $stats = [
            'open' => (clone $query)->where('status', 'aberto')->count(),
            'paid' => (clone $query)->where('status', 'pago')->count(),
            'late' => (clone $query)->where('status', 'atrasado')->count(),
        ];

        $charges = $query->paginate(12);

        return view('livewire.resident.charges', [
            'charges' => $charges,
            'apartments' => $user->apartments()->with('block')->get(),
            'stats' => $stats,
        ])->layout('components.layouts.app', [
            'title' => __('Minhas cobranças de água'),
        ]);
    }
}
