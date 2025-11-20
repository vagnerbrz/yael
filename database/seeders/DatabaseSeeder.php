<?php

namespace Database\Seeders;

use App\Models\Apartment;
use App\Models\Block;
use App\Models\Correspondence;
use App\Models\User;
use App\Models\WaterCharge;
use Illuminate\Database\Seeder;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        $admin = User::factory()->create([
            'name' => 'Admin Condômino',
            'email' => 'admin@condominio.test',
            'document' => '00000000000',
            'phone' => '(11) 99999-0000',
            'role' => 'admin',
            'password' => 'password',
        ]);

        $blocks = $this->seedBlocks();
        $apartments = $this->seedApartments($blocks);

        $residents = User::factory()
            ->count(32)
            ->create();

        $this->assignResidentsToApartments($residents, $apartments);
        $this->seedWaterCharges($apartments, $admin);
        $this->seedCorrespondences($apartments, $admin);
    }

    protected function seedBlocks(): Collection
    {
        return collect(range(1, 11))->map(function ($number) {
            return Block::create([
                'name' => "Bloco {$number}",
                'description' => "Apartamentos do bloco {$number}",
            ]);
        });
    }

    protected function seedApartments(Collection $blocks): Collection
    {
        $apartments = collect();

        // Definição dos números corretos
        $sideANumbers = [
            101, 102, 103, 104,
            201, 202, 203, 204,
            301, 302, 303, 304
        ];

        $sideBNumbers = [
            105, 106, 107, 108,
            205, 206, 207, 208,
            305, 306, 307, 308
        ];

        foreach ($blocks as $block) {
            // Lado A
            foreach ($sideANumbers as $number) {
                $apartments->push(
                    Apartment::create([
                        'block_id' => $block->id,
                        'side' => 'A',
                        'number' => (string) $number,
                        'status' => 'ocupado', // ajuste se quiser lógica diferente
                    ])
                );
            }

            // Lado B
            foreach ($sideBNumbers as $number) {
                $apartments->push(
                    Apartment::create([
                        'block_id' => $block->id,
                        'side' => 'B',
                        'number' => (string) $number,
                        'status' => 'ocupado', // ajuste se quiser lógica diferente
                    ])
                );
            }
        }

        return $apartments;
    }

    protected function assignResidentsToApartments(Collection $residents, Collection $apartments): void
    {
        $apartments = $apartments->shuffle()->values();

        foreach ($residents as $index => $resident) {
            $apartment = $apartments[$index] ?? null;

            if (! $apartment) {
                break;
            }

            $apartment->residents()->attach($resident->id, [
                'responsibility_type' => $index % 3 === 0 ? 'proprietario' : 'inquilino',
                'is_primary' => true,
            ]);
        }
    }

    protected function seedWaterCharges(Collection $apartments, User $admin): void
    {
        $competence = Carbon::now()->format('Y-m');
        $dueDate = Carbon::now()->startOfMonth()->addDays(9);

        $apartments
            ->filter(fn ($apartment) => $apartment->status === 'ocupado')
            ->each(function ($apartment) use ($competence, $dueDate, $admin) {
                WaterCharge::firstOrCreate(
                    [
                        'apartment_id' => $apartment->id,
                        'competence' => $competence,
                    ],
                    [
                        'amount' => WaterCharge::DEFAULT_AMOUNT,
                        'due_date' => $dueDate,
                        'status' => 'aberto',
                        'recorded_by_user_id' => $admin->id,
                    ]
                );
            });
    }

    protected function seedCorrespondences(Collection $apartments, User $admin): void
    {
        $apartments
            ->shuffle()
            ->take(5)
            ->each(function ($apartment) use ($admin) {
                Correspondence::create([
                    'apartment_id' => $apartment->id,
                    'type' => 'Encomenda',
                    'carrier' => 'Correios',
                    'description' => 'Volume registrado automaticamente pela seed.',
                    'received_at' => Carbon::now()->subDays(random_int(0, 5)),
                    'status' => 'pendente',
                    'registered_by_user_id' => $admin->id,
                ]);
            });
    }
}
