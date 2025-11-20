<?php

use App\Livewire\Admin\ApartmentsManager;
use App\Livewire\Admin\CorrespondenceManager;
use App\Livewire\Admin\Reports;
use App\Livewire\Admin\ResidentsManager;
use App\Livewire\Admin\WaterChargesManager;
use App\Livewire\Dashboard;
use App\Livewire\Resident\Charges as ResidentCharges;
use App\Livewire\Resident\Correspondences as ResidentCorrespondences;
use App\Livewire\Settings\Appearance;
use App\Livewire\Settings\Password;
use App\Livewire\Settings\Profile;
use App\Livewire\Settings\TwoFactor;
use Illuminate\Support\Facades\Route;
use Laravel\Fortify\Features;

Route::get('/', function () {
    return redirect('/login');
})->name('home');

Route::middleware(['auth', 'verified'])->group(function () {
    Route::get('dashboard', Dashboard::class)->name('dashboard');

    Route::get('admin/moradores', ResidentsManager::class)->name('admin.residents');
    Route::get('admin/cobrancas', WaterChargesManager::class)->name('admin.water-charges');
    Route::get('admin/correspondencias', CorrespondenceManager::class)->name('admin.correspondences');
    Route::get('admin/relatorios', Reports::class)->name('admin.reports');
    Route::get('admin/apartamentos', ApartmentsManager::class)->name('admin.apartments');

    Route::get('morador/cobrancas', ResidentCharges::class)->name('resident.charges');
    Route::get('morador/correspondencias', ResidentCorrespondences::class)->name('resident.correspondences');
});

Route::middleware(['auth'])->group(function () {
    Route::redirect('settings', 'settings/profile');

    Route::get('settings/profile', Profile::class)->name('profile.edit');
    Route::get('settings/password', Password::class)->name('user-password.edit');
    Route::get('settings/appearance', Appearance::class)->name('appearance.edit');

    Route::get('settings/two-factor', TwoFactor::class)
        ->middleware(
            when(
                Features::canManageTwoFactorAuthentication()
                    && Features::optionEnabled(Features::twoFactorAuthentication(), 'confirmPassword'),
                ['password.confirm'],
                [],
            ),
        )
        ->name('two-factor.show');
});
