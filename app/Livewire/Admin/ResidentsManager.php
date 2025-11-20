<?php

namespace App\Livewire\Admin;

use App\Models\Apartment;
use App\Models\Block;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;
use Livewire\Component;
use Livewire\WithPagination;

class ResidentsManager extends Component
{
    use WithPagination;

    public string $search = '';
    public string $blockId = '';
    public string $side = '';

    public array $form = [
        'name' => '',
        'email' => '',
        'document' => '',
        'phone' => '',
        'apartment_id' => '',
        'responsibility_type' => 'inquilino',
        'is_primary' => true,
    ];

    protected $rules = [
        'form.name' => 'required|string|max:255',
        'form.email' => 'required|email|max:255|unique:users,email',
        'form.document' => 'nullable|string|max:20|unique:users,document',
        'form.phone' => 'nullable|string|max:20',
        'form.apartment_id' => 'required|exists:apartments,id',
        'form.responsibility_type' => 'required|in:proprietario,inquilino',
        'form.is_primary' => 'boolean',
    ];

    public function mount(): void
    {
        abort_unless(Auth::user()?->isAdmin(), 403);
    }

    public function updatingSearch(): void
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

    function randomDigits($digits = 8)
{
    if ($digits < 1) {
        throw new Exception('Digits must be >= 1');
    }

    $min = 10 ** ($digits - 1);
    $max = (10 ** $digits) - 1;

    return random_int($min, $max);
}

    public function saveResident(): void
    {
        $validated = $this->validate();

        $password = $this->randomDigits(6);

        $user = User::create([
            'name' => $validated['form']['name'],
            'email' => $validated['form']['email'],
            'document' => $validated['form']['document'] ?? null,
            'phone' => $validated['form']['phone'] ?? null,
            'role' => 'resident',
            'password' => $password,
        ]);

        $user->apartments()->syncWithoutDetaching([
            $validated['form']['apartment_id'] => [
                'responsibility_type' => $validated['form']['responsibility_type'],
                'is_primary' => (bool) $validated['form']['is_primary'],
            ],
        ]);

        $this->resetForm();

        session()->flash('status', __('Morador cadastrado com sucesso. Senha provisoria: :password', [
            'password' => $password,
        ]));
    }

    public function detachResident(int $apartmentId, int $userId): void
    {
        $apartment = Apartment::findOrFail($apartmentId);

        $apartment->residents()->detach($userId);

        session()->flash('status', __('Morador desvinculado do apartamento.'));
    }

    public function render()
    {
        $apartments = Apartment::with(['block', 'residents'])
            ->when($this->blockId !== '', fn ($query) => $query->where('block_id', $this->blockId))
            ->when($this->side !== '', fn ($query) => $query->where('side', $this->side))
            ->when($this->search, function ($query) {
                $query->where(function ($subQuery) {
                    $subQuery
                        ->where('number', 'like', "%{$this->search}%")
                        ->orWhereHas('residents', fn ($residentQuery) => $residentQuery->where('name', 'like', "%{$this->search}%"));
                });
            })
            ->orderBy('block_id')
            ->orderBy('side')
            ->orderBy('number')
            ->paginate(10);

        return view('livewire.admin.residents-manager', [
            'apartments' => $apartments,
            'blocks' => Block::with([
                'apartments' => fn ($query) => $query->orderBy('side')->orderBy('number')
            ])
            ->orderBy('created_at') // ou ->orderBy('created_at') se for sempre na ordem de criação
            ->get(),
        ])->layout('components.layouts.app', [
            'title' => __('Gestão de moradores'),
        ]);
    }

    protected function resetForm(): void
    {
        $this->form = [
            'name' => '',
            'email' => '',
            'document' => '',
            'phone' => '',
            'apartment_id' => '',
            'responsibility_type' => 'inquilino',
            'is_primary' => true,
        ];

        $this->resetValidation();
    }
}
