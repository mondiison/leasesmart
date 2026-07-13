<?php

use App\Enums\BillingCycle;
use App\Enums\InvoiceStatus;
use App\Enums\InvoiceType;
use App\Enums\PaymentMethod;
use App\Enums\PaymentStatus;
use App\Enums\PropertyPublishStatus;
use App\Enums\Role;
use App\Enums\TenancyStatus;
use App\Enums\UnitOccupancyStatus;
use App\Livewire\Billing\Index as BillingIndex;
use App\Models\Caretaker;
use App\Models\Invoice;
use App\Models\Landlord;
use App\Models\Payment;
use App\Models\Property;
use App\Models\PropertyUnit;
use App\Models\Receipt;
use App\Models\Tenant;
use App\Models\Tenancy;
use App\Models\User;
use App\Notifications\InvoiceIssuedNotification;
use App\Notifications\PaymentReviewedNotification;
use App\Notifications\PaymentSubmittedNotification;
use Database\Seeders\RoleAndPermissionSeeder;
use Illuminate\Support\Facades\Notification;
use Livewire\Livewire;

$makeAdmin = function (): User {
    $user = User::factory()->create(['email_verified_at' => now()]);
    $user->assignRole(Role::Admin->value);

    return $user;
};

$makeLandlord = function (string $company = 'Acme Estates'): User {
    $user = User::factory()->create(['email_verified_at' => now()]);
    $user->assignRole(Role::Landlord->value);
    Landlord::query()->create(['user_id' => $user->id, 'company_name' => $company]);

    return $user;
};

$makeTenant = function (): User {
    $user = User::factory()->create(['email_verified_at' => now()]);
    $user->assignRole(Role::Tenant->value);
    Tenant::query()->create(['user_id' => $user->id, 'full_name' => $user->name, 'email' => $user->email]);

    return $user;
};

$makeCaretaker = function (): User {
    $user = User::factory()->create(['email_verified_at' => now()]);
    $user->assignRole(Role::Caretaker->value);
    Caretaker::query()->create(['user_id' => $user->id, 'employee_code' => 'CT-001']);

    return $user;
};

$makeTenancy = function (User $admin, User $landlord, User $tenant, string $title = 'Maple Court'): Tenancy {
    $property = Property::factory()->create([
        'title' => $title,
        'landlord_id' => $landlord->landlordProfile->id,
        'publish_status' => PropertyPublishStatus::Published,
        'published_at' => now()->subDay(),
        'created_by' => $admin->id,
        'updated_by' => $admin->id,
    ]);

    $unit = PropertyUnit::factory()->create([
        'property_id' => $property->id,
        'occupancy_status' => UnitOccupancyStatus::Occupied,
        'billing_cycle' => BillingCycle::Yearly,
        'rent_amount' => 2500000,
        'service_charge_amount' => 250000,
    ]);

    return Tenancy::query()->create([
        'property_id' => $property->id,
        'property_unit_id' => $unit->id,
        'tenant_user_id' => $tenant->id,
        'created_by' => $admin->id,
        'updated_by' => $admin->id,
        'status' => TenancyStatus::Active,
        'tenant_name' => $tenant->name,
        'tenant_email' => $tenant->email,
        'tenant_phone' => '08030000000',
        'lease_start_date' => now()->subMonth()->toDateString(),
        'lease_end_date' => now()->addYear()->toDateString(),
        'rent_amount' => 2500000,
        'service_charge_amount' => 250000,
        'billing_cycle' => BillingCycle::Yearly,
    ]);
};

test('admins can issue tenancy invoices and notify tenants', function () use ($makeAdmin, $makeLandlord, $makeTenant, $makeTenancy) {
    $this->seed(RoleAndPermissionSeeder::class);

    $admin = $makeAdmin();
    $landlord = $makeLandlord();
    $tenant = $makeTenant();
    $tenancy = $makeTenancy($admin, $landlord, $tenant);

    Notification::fake();

    Livewire::actingAs($admin)
        ->test(BillingIndex::class)
        ->set("invoiceTypeSelections.{$tenancy->id}", InvoiceType::Rent->value)
        ->set("invoiceIssueDates.{$tenancy->id}", now()->toDateString())
        ->set("invoiceDueDates.{$tenancy->id}", now()->addDays(10)->toDateString())
        ->set("invoiceNotes.{$tenancy->id}", 'April rent invoice')
        ->call('createInvoice', $tenancy->id)
        ->assertHasNoErrors();

    $invoice = Invoice::query()->firstOrFail();

    expect($invoice->invoice_type)->toBe(InvoiceType::Rent)
        ->and((float) $invoice->total_amount)->toBe(2500000.0)
        ->and((float) $invoice->balance_amount)->toBe(2500000.0)
        ->and($invoice->status)->toBe(InvoiceStatus::Issued);

    expect($invoice->items)->toHaveCount(1);

    Notification::assertSentTo($tenant, InvoiceIssuedNotification::class);

    $this->assertDatabaseHas('activity_logs', [
        'action' => 'invoice_created',
        'loggable_id' => $invoice->id,
    ]);
});

test('tenants can submit payments and admins can verify them with receipts', function () use ($makeAdmin, $makeLandlord, $makeTenant, $makeTenancy) {
    $this->seed(RoleAndPermissionSeeder::class);

    $admin = $makeAdmin();
    $landlord = $makeLandlord();
    $tenant = $makeTenant();
    $tenancy = $makeTenancy($admin, $landlord, $tenant);

    $invoice = Invoice::query()->create([
        'tenancy_id' => $tenancy->id,
        'tenant_user_id' => $tenant->id,
        'invoice_number' => 'INV-TEST-001',
        'invoice_type' => InvoiceType::Rent,
        'issue_date' => now()->toDateString(),
        'due_date' => now()->addDays(7)->toDateString(),
        'subtotal_amount' => 2500000,
        'discount_amount' => 0,
        'total_amount' => 2500000,
        'balance_amount' => 2500000,
        'status' => InvoiceStatus::Issued,
        'issued_by' => $admin->id,
    ]);

    $invoice->items()->create([
        'item_type' => InvoiceType::Rent->value,
        'description' => 'Annual rent',
        'quantity' => 1,
        'unit_amount' => 2500000,
        'total_amount' => 2500000,
    ]);

    Notification::fake();

    $this->actingAs($tenant)
        ->get(route('billing.index'))
        ->assertOk()
        ->assertSee('My Billing')
        ->assertSee('Invoices and Payment Proof')
        ->assertSee('Payment timeline')
        ->assertSee('Submit payment proof');

    Livewire::actingAs($tenant)
        ->test(BillingIndex::class)
        ->set("paymentAmounts.{$invoice->id}", '2500000')
        ->set("paymentMethodSelections.{$invoice->id}", PaymentMethod::BankTransfer->value)
        ->set("paymentNotes.{$invoice->id}", 'Bank transfer sent')
        ->call('submitPayment', $invoice->id)
        ->assertHasNoErrors();

    $payment = Payment::query()->firstOrFail();

    expect($payment->status)->toBe(PaymentStatus::PendingVerification);
    Notification::assertSentTo($admin, PaymentSubmittedNotification::class);

    Livewire::actingAs($admin)
        ->test(BillingIndex::class)
        ->set("reviewStatuses.{$payment->id}", PaymentStatus::Verified->value)
        ->set("reviewNotes.{$payment->id}", 'Funds confirmed')
        ->call('reviewPayment', $payment->id)
        ->assertHasNoErrors();

    $invoice->refresh();
    $payment->refresh();

    expect($payment->status)->toBe(PaymentStatus::Verified)
        ->and($invoice->status)->toBe(InvoiceStatus::Paid)
        ->and((float) $invoice->balance_amount)->toBe(0.0);

    expect(Receipt::query()->count())->toBe(1);
    expect($payment->receipt()->exists())->toBeTrue();

    Notification::assertSentTo($tenant, PaymentReviewedNotification::class);

    $this->assertDatabaseHas('payment_allocations', [
        'payment_id' => $payment->id,
        'invoice_id' => $invoice->id,
        'amount' => 2500000,
    ]);
});

test('landlords only see billing for their own portfolio and caretakers cannot access billing', function () use ($makeAdmin, $makeLandlord, $makeTenant, $makeCaretaker, $makeTenancy) {
    $this->seed(RoleAndPermissionSeeder::class);

    $admin = $makeAdmin();
    $landlordA = $makeLandlord('North Star Estates');
    $landlordB = $makeLandlord('South Star Estates');
    $tenantA = $makeTenant();
    $tenantB = $makeTenant();
    $caretaker = $makeCaretaker();

    $tenancyA = $makeTenancy($admin, $landlordA, $tenantA, 'North Star Court');
    $tenancyB = $makeTenancy($admin, $landlordB, $tenantB, 'South Star Court');

    Invoice::query()->create([
        'tenancy_id' => $tenancyA->id,
        'tenant_user_id' => $tenantA->id,
        'invoice_number' => 'INV-LA-001',
        'invoice_type' => InvoiceType::Rent,
        'issue_date' => now()->toDateString(),
        'due_date' => now()->addDays(7)->toDateString(),
        'subtotal_amount' => 2500000,
        'discount_amount' => 0,
        'total_amount' => 2500000,
        'balance_amount' => 2500000,
        'status' => InvoiceStatus::Issued,
        'issued_by' => $admin->id,
    ]);

    Invoice::query()->create([
        'tenancy_id' => $tenancyB->id,
        'tenant_user_id' => $tenantB->id,
        'invoice_number' => 'INV-LB-001',
        'invoice_type' => InvoiceType::Rent,
        'issue_date' => now()->toDateString(),
        'due_date' => now()->addDays(7)->toDateString(),
        'subtotal_amount' => 2500000,
        'discount_amount' => 0,
        'total_amount' => 2500000,
        'balance_amount' => 2500000,
        'status' => InvoiceStatus::Issued,
        'issued_by' => $admin->id,
    ]);

    $this->actingAs($landlordA)
        ->get(route('billing.index'))
        ->assertOk()
        ->assertSee('INV-LA-001')
        ->assertDontSee('INV-LB-001');

    $this->actingAs($caretaker)
        ->get(route('billing.index'))
        ->assertForbidden();
});
