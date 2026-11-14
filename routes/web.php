<?php

use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\FamilyCardController;
use App\Http\Controllers\FamilyController;
use App\Http\Controllers\FamilyVerificationController;
use App\Http\Controllers\MemberController;
use App\Http\Controllers\RegionController;
use App\Http\Controllers\SettingController;
use App\Http\Controllers\UserController;
use Illuminate\Support\Facades\Route;

Route::middleware('guest')->group(function (): void {
    Route::get('/login', [LoginController::class, 'create'])->name('login');
    Route::post('/login', [LoginController::class, 'store'])->name('login.store');
});

Route::post('/logout', [LoginController::class, 'destroy'])
    ->middleware('auth')
    ->name('logout');

// Halaman verifikasi publik yang dibuka dari QR pada kartu KKJ.
Route::get('/verifikasi/{token}', [FamilyVerificationController::class, 'show'])
    ->where('token', '[A-Za-z0-9]+')
    ->middleware('throttle:120,1')
    ->name('families.verify');

Route::middleware('auth')->group(function (): void {
    Route::get('/', DashboardController::class)->name('dashboard');

    Route::get('/keluarga-saya', [FamilyController::class, 'mine'])->name('my.family');
    Route::get('/profil-saya', [MemberController::class, 'me'])->name('my.profile');

    Route::get('/pengaturan', [SettingController::class, 'edit'])->name('settings.edit');
    Route::put('/pengaturan', [SettingController::class, 'update'])->name('settings.update');
    Route::delete('/pengaturan/reset-data', [SettingController::class, 'resetData'])->name('settings.reset-data');
    Route::post('/pengaturan/impor', [SettingController::class, 'import'])->name('settings.import');
    Route::get('/pengaturan/impor/template', [SettingController::class, 'template'])->name('settings.import.template');

    Route::prefix('wilayah')->name('regions.')->middleware('throttle:120,1')->group(function (): void {
        Route::get('/provinsi', [RegionController::class, 'provinces'])->name('provinces');
        Route::get('/kabupaten', [RegionController::class, 'cities'])->name('cities');
        Route::get('/kecamatan', [RegionController::class, 'districts'])->name('districts');
        Route::get('/kelurahan', [RegionController::class, 'villages'])->name('villages');
        Route::get('/kodepos', [RegionController::class, 'postalCodes'])->name('postal-codes');
    });

    Route::get('/families/{family}/card', [FamilyCardController::class, 'show'])->name('families.card');
    Route::get('/families/{family}/card/download', [FamilyCardController::class, 'download'])->name('families.card.download');

    Route::resource('families', FamilyController::class);
    Route::resource('members', MemberController::class);
    Route::resource('users', UserController::class)->except(['show']);
});
