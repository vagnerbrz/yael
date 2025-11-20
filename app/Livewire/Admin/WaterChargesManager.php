<?php

namespace App\Livewire\Admin;

use App\Models\Apartment;
use App\Models\Block;
use App\Models\WaterCharge;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Auth;
use Livewire\Component;
use Livewire\WithPagination;

class WaterChargesManager extends Component
{
    use WithPagination;

    public string $competence;
    public string $statusFilter = '';
    public string $blockId = '';
    public string $side = '';

    protected $rules = [
        'competence' => 'required|date_format:Y-m',
    ];

    public function mount(): void
    {
        abort_unless(Auth::user()?->isAdmin(), 403);

        $this->competence = Carbon::now()->format('Y-m');
    }

    public function updatedCompetence(): void
    {
        $this->validateOnly('competence');
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

    public function generateCharges(): void
    {
        $data = $this->validate();

        $competence = $data['competence'];
        $dueDate = Carbon::createFromFormat('Y-m', $competence)->day(10);

        $apartments = Apartment::occupied()->get();
        $created = 0;

        foreach ($apartments as $apartment) {
            $charge = WaterCharge::firstOrCreate(
                [
                    'apartment_id' => $apartment->id,
                    'competence' => $competence,
                ],
                [
                    'amount' => WaterCharge::DEFAULT_AMOUNT,
                    'due_date' => $dueDate,
                    'status' => 'aberto',
                    'recorded_by_user_id' => Auth::id(),
                ]
            );

            if ($charge->wasRecentlyCreated) {
                $created++;
            }
        }

        session()->flash('status', trans_choice(
            '{0} Nenhum lancamento novo.|{1} :count lancamento criado.|[2,*] :count lancamentos criados.',
            $created,
            ['count' => $created]
        ));
    }

    public function toggleStatus(int $chargeId): void
    {
        $charge = WaterCharge::findOrFail($chargeId);

        if ($charge->status === 'pago') {
            $charge->markAsOpen();
        } else {
            $charge->markAsPaid(Auth::id());
        }
    }

    public function render()
    {
        $statsQuery = WaterCharge::where('competence', $this->competence);
        $stats = [
            'open' => (clone $statsQuery)->where('status', 'aberto')->count(),
            'paid' => (clone $statsQuery)->where('status', 'pago')->count(),
            'late' => (clone $statsQuery)->where('status', 'atrasado')->count(),
            'total_amount' => (clone $statsQuery)->sum('amount'),
        ];

        $charges = $this->filteredQuery()->paginate(15);

        return view('livewire.admin.water-charges-manager', [
            'charges' => $charges,
            'blocks' => Block::with([
                'apartments' => fn ($query) => $query->orderBy('side')->orderBy('number')
            ])
            ->orderBy('created_at') // ou ->orderBy('created_at') se for sempre na ordem de criação
            ->get(),
            'stats' => $stats,
        ])->layout('components.layouts.app', [
            'title' => __('Taxas de água'),
        ]);
    }

    protected function filteredQuery()
    {
        return WaterCharge::with(['apartment.block', 'recordedBy'])
            ->where('competence', $this->competence)
            ->when($this->statusFilter !== '', fn ($query) => $query->where('status', $this->statusFilter))
            ->when($this->blockId !== '', fn ($query) => $query->whereHas('apartment', fn ($sub) => $sub->where('block_id', $this->blockId)))
            ->when($this->side !== '', fn ($query) => $query->whereHas('apartment', fn ($sub) => $sub->where('side', $this->side)))
            ->orderBy('due_date');
    }
}
