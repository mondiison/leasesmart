<?php

use App\Enums\Role;
use App\Models\ActivityLog;
use App\Models\Tenant;
use App\Models\User;
use Database\Seeders\RoleAndPermissionSeeder;
use Illuminate\Auth\Events\Registered;
use Spatie\Permission\Models\Role as SpatieRole;

test('phase zero roles are seeded', function () {
    $this->seed(RoleAndPermissionSeeder::class);

    foreach (Role::cases() as $role) {
        expect(SpatieRole::where('name', $role->value)->exists())->toBeTrue();
    }
});

test('dashboard shows role-aware workspace content', function () {
    $this->seed(RoleAndPermissionSeeder::class);

    $user = User::factory()->create([
        'email_verified_at' => now(),
    ]);
    $user->assignRole(Role::Landlord->value);

    $response = $this->actingAs($user)->get('/dashboard');

    $response
        ->assertOk()
        ->assertSee('Portfolio View')
        ->assertSee('Portfolio Health');
});

test('inactive users cannot access the dashboard', function () {
    $this->seed(RoleAndPermissionSeeder::class);

    $user = User::factory()->create([
        'is_active' => false,
        'email_verified_at' => now(),
    ]);
    $user->assignRole(Role::Tenant->value);

    $response = $this->actingAs($user)->get('/dashboard');

    $response->assertRedirect('/login');
    $this->assertGuest();
});

test('register-ready users can be provisioned with a tenant profile', function () {
    $this->seed(RoleAndPermissionSeeder::class);

    $user = User::create([
        'name' => 'New Tenant',
        'email' => 'new-tenant@example.com',
        'password' => 'password',
    ]);
    $user->assignRole(Role::Tenant->value);
    $user->tenantProfile()->create([
        'full_name' => $user->name,
        'email' => $user->email,
    ]);

    expect($user->fresh()->hasRole(Role::Tenant->value))->toBeTrue();
    expect(Tenant::where('user_id', $user->id)->exists())->toBeTrue();
});

test('auth activity can be recorded', function () {
    $user = User::factory()->create();

    event(new Registered($user));

    expect(ActivityLog::where('user_id', $user->id)->where('action', 'registered')->exists())->toBeTrue();
});
