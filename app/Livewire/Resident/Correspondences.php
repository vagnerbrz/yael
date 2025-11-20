<?php

namespace App\Livewire\Resident;

use App\Models\Correspondence;
use Illuminate\Support\Facades\Auth;
use Livewire\Component;
use Livewire\WithPagination;

class Correspondences extends Component
{
    use WithPagination;

    public string $apartmentId = '';

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

    public function confirmPickup(int $correspondenceId): void
    {
        $correspondence = Correspondence::whereIn('apartment_id', $this->apartmentIds())
            ->findOrFail($correspondenceId);

        $correspondence->markAsRetrieved(Auth::user()->name);

        session()->flash('status', __('Retirada registrada com sucesso.'));
    }

    public function render()
    {
        $aptIds = $this->apartmentIds();

        $pending = Correspondence::with('apartment.block')
            ->whereIn('apartment_id', $aptIds)
            ->when($this->apartmentId !== '', fn ($query) => $query->where('apartment_id', $this->apartmentId))
            ->where('status', 'pendente')
            ->orderByDesc('received_at')
            ->get();

        $historyQuery = Correspondence::with('apartment.block')
            ->whereIn('apartment_id', $aptIds)
            ->when($this->apartmentId !== '', fn ($query) => $query->where('apartment_id', $this->apartmentId))
            ->where('status', 'retirado')
            ->orderByDesc('retrieved_at');

        return view('livewire.resident.correspondences', [
            'pending' => $pending,
            'history' => $historyQuery->paginate(10),
            'apartments' => Auth::user()->apartments()->with('block')->get(),
        ])->layout('components.layouts.app', [
            'title' => __('Minhas correspondências'),
        ]);
    }

    protected function apartmentIds()
    {
        return Auth::user()->apartments()->pluck('apartments.id');
    }
}
