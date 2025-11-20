<?php

namespace App\Livewire\Admin;

use App\Models\Apartment;
use App\Models\Block;
use App\Models\Correspondence;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Auth;
use Livewire\Component;
use Livewire\WithPagination;

class CorrespondenceManager extends Component
{
    use WithPagination;

    public array $form = [
        'apartment_id' => '',
        'type' => '',
        'carrier' => '',
        'description' => '',
        'received_at' => '',
    ];

    public array $retrievalNames = [];

    public string $blockId = '';
    public string $side = '';
    public string $statusFilter = 'pendente';
    public string $search = '';

    protected $rules = [
        'form.apartment_id' => 'required|exists:apartments,id',
        'form.type' => 'required|string|max:100',
        'form.carrier' => 'nullable|string|max:100',
        'form.description' => 'nullable|string|max:500',
        'form.received_at' => 'nullable|date',
    ];

    public function mount(): void
    {
        abort_unless(Auth::user()?->isAdmin(), 403);
    }

    public function saveCorrespondence(): void
    {
        $data = $this->validate();

        Correspondence::create([
            'apartment_id' => $data['form']['apartment_id'],
            'type' => $data['form']['type'],
            'carrier' => $data['form']['carrier'] ?? null,
            'description' => $data['form']['description'] ?? null,
            'received_at' => $data['form']['received_at']
                ? Carbon::parse($data['form']['received_at'])
                : Carbon::now(),
            'registered_by_user_id' => Auth::id(),
        ]);

        $this->resetForm();
        $this->resetPage();

        session()->flash('status', __('Correspondencia registrada com sucesso.'));
    }

    public function markAsRetrieved(int $correspondenceId): void
    {
        $correspondence = Correspondence::findOrFail($correspondenceId);
        $name = $this->retrievalNames[$correspondenceId] ?? Auth::user()->name;

        $correspondence->markAsRetrieved($name);

        unset($this->retrievalNames[$correspondenceId]);

        session()->flash('status', __('Retirada confirmada.'));
    }

    public function updatingSearch(): void
    {
        $this->resetPage();
    }

    public function updatingStatusFilter(): void
    {
        $this->resetPage();
    }

    public function updatingBlockId(): void
    {
        $this->resetPage();
    }

    public function updatingSide(): void
    {
        $this->resetPage();
    }

    public function render()
    {
        $correspondences = Correspondence::with(['apartment.block'])
            ->when($this->statusFilter !== '', fn ($query) => $query->where('status', $this->statusFilter))
            ->when($this->blockId !== '', fn ($query) => $query->whereHas('apartment', fn ($sub) => $sub->where('block_id', $this->blockId)))
            ->when($this->side !== '', fn ($query) => $query->whereHas('apartment', fn ($sub) => $sub->where('side', $this->side)))
            ->when($this->search !== '', function ($query) {
                $query->where(function ($subQuery) {
                    $subQuery
                        ->where('type', 'like', "%{$this->search}%")
                        ->orWhereHas('apartment', fn ($apartmentQuery) => $apartmentQuery->where('number', 'like', "%{$this->search}%"));
                });
            })
            ->orderByDesc('received_at')
            ->paginate(12);

        return view('livewire.admin.correspondence-manager', [
            'correspondences' => $correspondences,
            'blocks' => Block::with([
                'apartments' => fn ($query) => $query->orderBy('side')->orderBy('number')
            ])
            ->orderBy('created_at') // ou ->orderBy('created_at') se for sempre na ordem de criação
            ->get(),
        ])->layout('components.layouts.app', [
            'title' => __('Correspondências'),
        ]);
    }

    protected function resetForm(): void
    {
        $this->form = [
            'apartment_id' => '',
            'type' => '',
            'carrier' => '',
            'description' => '',
            'received_at' => '',
        ];

        $this->resetValidation();
    }
}
