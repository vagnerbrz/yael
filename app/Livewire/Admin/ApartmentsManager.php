<?php

namespace App\Livewire\Admin;

use App\Models\Apartment;
use App\Models\Block;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;
use Livewire\Component;
use Livewire\WithPagination;

class ApartmentsManager extends Component
{
    use WithPagination;

    public string $search = '';
    public string $blockFilter = '';
    public string $sideFilter = '';
    public string $statusFilter = '';

    public ?int $editingId = null;

    public array $form = [
        'block_id' => '',
        'side' => 'A',
        'number' => '',
        'status' => 'ocupado',
        'notes' => '',
    ];

    protected $listeners = [
        'confirmDelete' => 'delete',
    ];

    public function mount(): void
    {
        abort_unless(Auth::user()?->isAdmin(), 403);
    }

    public function updatingSearch(): void
    {
        $this->resetPage();
    }

    public function updatingBlockFilter(): void
    {
        $this->resetPage();
    }

    public function updatingSideFilter(): void
    {
        $this->resetPage();
    }

    public function updatingStatusFilter(): void
    {
        $this->resetPage();
    }

    public function edit(int $apartmentId): void
    {
        $apartment = Apartment::findOrFail($apartmentId);

        $this->editingId = $apartment->id;
        $this->form = [
            'block_id' => $apartment->block_id,
            'side' => $apartment->side,
            'number' => $apartment->number,
            'status' => $apartment->status,
            'notes' => $apartment->notes,
        ];
    }

    public function cancelEdit(): void
    {
        $this->resetForm();
    }

    public function save(): void
    {
        $validated = $this->validate($this->rules());

        if ($this->editingId) {
            $apartment = Apartment::findOrFail($this->editingId);
            $apartment->update($validated['form']);

            session()->flash('status', __('Apartamento atualizado com sucesso.'));
        } else {
            Apartment::create($validated['form']);

            session()->flash('status', __('Apartamento criado com sucesso.'));
        }

        $this->resetForm();
    }

    public function delete(int $apartmentId): void
    {
        Apartment::findOrFail($apartmentId)->delete();

        session()->flash('status', __('Apartamento removido.'));

        if ($this->editingId === $apartmentId) {
            $this->resetForm();
        }
    }

    public function render()
    {
        $apartments = Apartment::with('block')
            ->when($this->blockFilter !== '', fn ($query) => $query->where('block_id', $this->blockFilter))
            ->when($this->sideFilter !== '', fn ($query) => $query->where('side', $this->sideFilter))
            ->when($this->statusFilter !== '', fn ($query) => $query->where('status', $this->statusFilter))
            ->when($this->search !== '', function ($query) {
                $query->where(function ($sub) {
                    $sub->where('number', 'like', "%{$this->search}%")
                        ->orWhereHas('block', fn ($blockQuery) => $blockQuery->where('name', 'like', "%{$this->search}%"));
                });
            })
            ->orderBy('block_id')
            ->orderBy('side')
            ->orderBy('number')
            ->paginate(12);

        return view('livewire.admin.apartments-manager', [
            'apartments' => $apartments,
            'blocks' => Block::with([
                'apartments' => fn ($query) => $query->orderBy('side')->orderBy('number')
            ])
            ->orderBy('created_at') // ou ->orderBy('created_at') se for sempre na ordem de criação
            ->get(),
        ])->layout('components.layouts.app', [
            'title' => __('Gestão de apartamentos'),
        ]);
    }

    protected function rules(): array
    {
        return [
            'form.block_id' => ['required', 'exists:blocks,id'],
            'form.side' => ['required', Rule::in(['A', 'B'])],
            'form.number' => [
                'required',
                'string',
                'max:10',
                Rule::unique('apartments', 'number')
                    ->ignore($this->editingId)
                    ->where(fn ($query) => $query
                        ->where('block_id', $this->form['block_id'])
                        ->where('side', $this->form['side'])),
            ],
            'form.status' => ['required', Rule::in(['ocupado', 'vago'])],
            'form.notes' => ['nullable', 'string', 'max:500'],
        ];
    }

    protected function resetForm(): void
    {
        $this->editingId = null;
        $this->form = [
            'block_id' => '',
            'side' => 'A',
            'number' => '',
            'status' => 'ocupado',
            'notes' => '',
        ];

        $this->resetValidation();
    }
}
