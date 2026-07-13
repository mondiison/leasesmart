<?php

use App\Http\Controllers\DashboardController;
use App\Http\Controllers\ExportController;
use App\Http\Controllers\NotificationController;
use App\Http\Controllers\PremiumReportController;
use App\Http\Controllers\SearchController;
use App\Http\Controllers\TenancyDocumentController;
use App\Livewire\Admin\ManageUser as AdminManageUser;
use App\Livewire\Admin\RolesIndex as AdminRolesIndex;
use App\Livewire\Admin\UsersIndex as AdminUsersIndex;
use App\Livewire\Applications\Index as ApplicationsIndex;
use App\Livewire\Billing\Index as BillingIndex;
use App\Livewire\Inspections\Index as InspectionsIndex;
use App\Livewire\Maintenance\Index as MaintenanceIndex;
use App\Livewire\Marketplace\Index as MarketplaceIndex;
use App\Livewire\Marketplace\Show as MarketplaceShow;
use App\Livewire\Properties\Index as PropertiesIndex;
use App\Livewire\Properties\ManageProperty;
use App\Livewire\Properties\ManageUnit;
use App\Livewire\Tenancies\Index as TenanciesIndex;
use Illuminate\Support\Facades\Route;
use Livewire\Volt\Volt;

Route::get('/', MarketplaceIndex::class)->name('home');
Route::get('listings', MarketplaceIndex::class)->name('marketplace.index');
Route::get('listings/{property:slug}', MarketplaceShow::class)->name('marketplace.show');

Route::get('dashboard', DashboardController::class)
    ->middleware(['auth', 'active', 'verified'])
    ->name('dashboard');

Route::middleware(['auth', 'active'])->group(function () {
    Route::redirect('settings', 'settings/profile');

    Volt::route('settings/profile', 'settings.profile')->name('settings.profile');
    Volt::route('settings/password', 'settings.password')->name('settings.password');
    Volt::route('settings/appearance', 'settings.appearance')->name('settings.appearance');

    Route::get('notifications', [NotificationController::class, 'index'])->name('notifications.index');
    Route::get('notifications/{notification}/open', [NotificationController::class, 'open'])->name('notifications.open');
    Route::post('notifications/read-all', [NotificationController::class, 'markAllRead'])->name('notifications.read-all');
});

Route::middleware(['auth', 'active', 'verified'])->group(function () {
    Route::get('search', SearchController::class)->name('search.index');
    Route::get('exports/{type}', ExportController::class)->name('exports.show');
    Route::get('reports/{type}/premium', PremiumReportController::class)->name('reports.premium');
    Route::get('properties', PropertiesIndex::class)->name('properties.index');
    Route::get('properties/create', ManageProperty::class)->name('properties.create');
    Route::get('properties/{property}/edit', ManageProperty::class)->name('properties.edit');
    Route::get('properties/{property}/units/create', ManageUnit::class)->name('properties.units.create');
    Route::get('properties/{property}/units/{unit}/edit', ManageUnit::class)->name('properties.units.edit');
    Route::get('inspections', InspectionsIndex::class)->name('inspections.index');
    Route::get('applications', ApplicationsIndex::class)->name('applications.index');
    Route::get('tenancies', TenanciesIndex::class)->name('tenancies.index');
    Route::get('tenancies/documents/{media}', TenancyDocumentController::class)->name('tenancies.documents.show');
    Route::get('billing', BillingIndex::class)->name('billing.index');
    Route::get('maintenance', MaintenanceIndex::class)->name('maintenance.index');
});

Route::middleware(['auth', 'active', 'verified', 'role:admin'])->prefix('admin')->as('admin.')->group(function () {
    Route::get('users', AdminUsersIndex::class)->name('users.index');
    Route::get('users/create', AdminManageUser::class)->name('users.create');
    Route::get('users/{user}/edit', AdminManageUser::class)->name('users.edit');
    Route::get('roles', AdminRolesIndex::class)->name('roles.index');
});

require __DIR__.'/auth.php';
