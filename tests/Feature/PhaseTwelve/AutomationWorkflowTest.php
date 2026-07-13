<?php

use App\Enums\BillingCycle;
use App\Enums\InvoiceType;
use App\Enums\Role;
use App\Enums\TenancyStatus;
use App\Enums\UnitOccupancyStatus;
use App\Models\Caretaker;
use App\Models\Invoice;
use App\Models\Landlord;
use App\Models\Property;
use App\Models\PropertyUnit;
use App\Models\Tenancy;
use App\Models\Tenant;
use App\Models\User;
use App\Notifications\InvoiceIssuedNotification;
use App\Notifications\LeaseExpiryAlertNotification;
use App\Notifications\WeeklyReportDigestNotification;
use Database\Seeders\RoleAndPermissionSeeder;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Notification;

function makeAutomationAdmin(): User
{
    $user = User::factory()->create(['email_verified_at' => now(), 'is_active' => true]);
    $user->assignRole(Role::Admin->value);

    return $user;
}

function makeAutomationTenancy(array $attributes = []): Tenancy
{
    $landlordUser = User::factory()->create(['email_verified_at' => now()]);
    $landlordUser->assignRole(Role::Landlord->value);
    $landlord = Landlord::query()->create(['user_id' => $landlordUser->id, 'company_name' => 'Automation Estates']);

    $caretakerUser = User::factory()->create(['email_verified_at' => now()]);
    $caretakerUser->assignRole(Role::Caretaker->value);
    $caretaker = Caretaker::query()->create(['user_id' => $caretakerUser->id, 'employee_code' => 'AUTO-CT']);

    $tenantUser = User::factory()->create(['email_verified_at' => now()]);
    $tenantUser->assignRole(Role::Tenant->value);
    Tenant::query()->create(['user_id' => $tenantUser->id, 'full_name' => $tenantUser->name, 'email' => $tenantUser->email]);

    $property = Property::factory()->create([
        'landlord_id' => $landlord->id,
        'caretaker_id' => $caretaker->id,
    ]);

    $unit = PropertyUnit::factory()->create([
        'property_id' => $property->id,
        'occupancy_status' => UnitOccupancyStatus::Occupied,
    ]);

    return Tenancy::query()->create(array_merge([
        'property_id' => $property->id,
        'property_unit_id' => $unit->id,
        'tenant_user_id' => $tenantUser->id,
        'status' => TenancyStatus::Active,
        'tenant_name' => $tenantUser->name,
        'tenant_email' => $tenantUser->email,
        'tenant_phone' => '+2348000000100',
        'lease_start_date' => '2026-01-15',
        'lease_end_date' => '2027-01-14',
        'move_in_date' => '2026-01-15',
        'activated_at' => '2026-01-15 08:00:00',
        'rent_amount' => 1200000,
        'service_charge_amount' => 150000,
        'billing_cycle' => BillingCycle::Monthly,
    ], $attributes));
}

test('recurring rent invoice command creates one invoice per active billing period', function () {
    Notification::fake();
    $this->seed(RoleAndPermissionSeeder::class);

    $admin = makeAutomationAdmin();
    $tenancy = makeAutomationTenancy();

    Artisan::call('app:billing-generate-rent-invoices', [
        '--actor' => $admin->id,
        '--date' => '2026-04-25',
    ]);

    $invoice = Invoice::query()->where('tenancy_id', $tenancy->id)->firstOrFail();

    expect($invoice->invoice_type)->toBe(InvoiceType::Rent);
    expect($invoice->issue_date->toDateString())->toBe('2026-04-15');
    expect((float) $invoice->total_amount)->toBe(1200000.0);

    Notification::assertSentTo($tenancy->tenantUser, InvoiceIssuedNotification::class);

    Artisan::call('app:billing-generate-rent-invoices', [
        '--actor' => $admin->id,
        '--date' => '2026-04-25',
    ]);

    expect(Invoice::query()->where('tenancy_id', $tenancy->id)->count())->toBe(1);
});

test('lease expiry alert command notifies tenancy stakeholders once per milestone', function () {
    Notification::fake();
    $this->seed(RoleAndPermissionSeeder::class);

    $admin = makeAutomationAdmin();
    $tenancy = makeAutomationTenancy([
        'lease_start_date' => '2025-05-01',
        'lease_end_date' => '2026-05-25',
    ])->load(['property.landlord.user', 'property.caretaker.user', 'tenantUser']);

    Artisan::call('app:tenancies-send-expiry-alerts', [
        '--actor' => $admin->id,
        '--date' => '2026-04-25',
    ]);

    Notification::assertSentTo($tenancy->tenantUser, LeaseExpiryAlertNotification::class);
    Notification::assertSentTo($tenancy->property->landlord->user, LeaseExpiryAlertNotification::class);
    Notification::assertSentTo($tenancy->property->caretaker->user, LeaseExpiryAlertNotification::class);

    $this->assertDatabaseHas('activity_logs', [
        'action' => 'lease_expiry_alert_sent',
        'loggable_id' => $tenancy->id,
    ]);

    Artisan::call('app:tenancies-send-expiry-alerts', [
        '--actor' => $admin->id,
        '--date' => '2026-04-25',
    ]);

    expect(
        \App\Models\ActivityLog::query()
            ->where('action', 'lease_expiry_alert_sent')
            ->where('loggable_id', $tenancy->id)
            ->count()
    )->toBe(1);
});

test('weekly report digest command notifies admins and landlords once per week', function () {
    Notification::fake();
    $this->seed(RoleAndPermissionSeeder::class);

    $admin = makeAutomationAdmin();

    $landlord = User::factory()->create(['email_verified_at' => now(), 'is_active' => true]);
    $landlord->assignRole(Role::Landlord->value);
    Landlord::query()->create(['user_id' => $landlord->id, 'company_name' => 'Digest Estates']);

    $tenant = User::factory()->create(['email_verified_at' => now(), 'is_active' => true]);
    $tenant->assignRole(Role::Tenant->value);
    Tenant::query()->create(['user_id' => $tenant->id, 'full_name' => $tenant->name, 'email' => $tenant->email]);

    Artisan::call('app:reports-send-weekly', [
        '--actor' => $admin->id,
        '--date' => '2026-04-26',
    ]);

    Notification::assertSentTo($admin, WeeklyReportDigestNotification::class);
    Notification::assertSentTo($landlord, WeeklyReportDigestNotification::class);
    Notification::assertNotSentTo($tenant, WeeklyReportDigestNotification::class);

    $this->assertDatabaseHas('activity_logs', [
        'action' => 'weekly_report_digest_sent',
        'loggable_id' => $admin->id,
    ]);

    Artisan::call('app:reports-send-weekly', [
        '--actor' => $admin->id,
        '--date' => '2026-04-26',
    ]);

    expect(
        \App\Models\ActivityLog::query()
            ->where('action', 'weekly_report_digest_sent')
            ->where('loggable_id', $admin->id)
            ->count()
    )->toBe(1);
});
