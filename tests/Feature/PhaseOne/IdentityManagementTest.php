<?php

use App\Enums\Role;
use App\Livewire\Admin\ManageUser;
use App\Models\Tenant;
use App\Models\User;
use Database\Seeders\RoleAndPermissionSeeder;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Livewire\Livewire;
use Livewire\Volt\Volt;

function createAdminUser(): User
{
    $admin = User::factory()->create([
        'email_verified_at' => now(),
    ]);
    $admin->assignRole(Role::Admin->value);

    return $admin;
}

test('admins can create users and sync tenant profiles', function () {
    $this->seed(RoleAndPermissionSeeder::class);
    $admin = createAdminUser();

    Livewire::actingAs($admin)
        ->test(ManageUser::class)
        ->set('name', 'Managed Tenant')
        ->set('email', 'managed-tenant@example.com')
        ->set('phone', '+2348012345678')
        ->set('bio', 'New resident account.')
        ->set('role', Role::Tenant->value)
        ->set('password', 'password')
        ->set('password_confirmation', 'password')
        ->set('is_active', true)
        ->set('profile.full_name', 'Managed Tenant')
        ->set('profile.employment_status', 'Employed')
        ->set('profile.employer_name', 'Acme Ltd')
        ->set('profile.monthly_income', 250000)
        ->call('save')
        ->assertHasNoErrors();

    $managedUser = User::where('email', 'managed-tenant@example.com')->firstOrFail();

    expect($managedUser->hasRole(Role::Tenant->value))->toBeTrue();
    expect(Tenant::where('user_id', $managedUser->id)->exists())->toBeTrue();
    expect($managedUser->notifications()->count())->toBeGreaterThan(0);
});

test('admins can view the role matrix page', function () {
    $this->seed(RoleAndPermissionSeeder::class);
    $admin = createAdminUser();

    $this->actingAs($admin)
        ->get(route('admin.roles.index'))
        ->assertOk()
        ->assertSee('Roles and Permissions')
        ->assertSee('Admin');
});

test('non admins cannot access admin user management', function () {
    $this->seed(RoleAndPermissionSeeder::class);

    $tenant = User::factory()->create([
        'email_verified_at' => now(),
    ]);
    $tenant->assignRole(Role::Tenant->value);

    $this->actingAs($tenant)
        ->get(route('admin.users.index'))
        ->assertForbidden();
});

test('admins can activate or deactivate managed accounts', function () {
    $this->seed(RoleAndPermissionSeeder::class);
    $admin = createAdminUser();

    $managedUser = User::factory()->create([
        'email_verified_at' => now(),
        'is_active' => true,
    ]);
    $managedUser->assignRole(Role::Tenant->value);

    Livewire::actingAs($admin)
        ->test(ManageUser::class, ['user' => $managedUser])
        ->call('toggleActive')
        ->assertHasNoErrors();

    expect($managedUser->fresh()->is_active)->toBeFalse();
});

test('admins can send password reset links to managed users', function () {
    $this->seed(RoleAndPermissionSeeder::class);
    $admin = createAdminUser();

    $managedUser = User::factory()->create([
        'email_verified_at' => now(),
    ]);
    $managedUser->assignRole(Role::Landlord->value);

    Livewire::actingAs($admin)
        ->test(ManageUser::class, ['user' => $managedUser])
        ->call('sendPasswordReset')
        ->assertHasNoErrors();

    $this->assertDatabaseHas('password_reset_tokens', [
        'email' => $managedUser->email,
    ]);
});

test('profile updates can store phone bio and avatar', function () {
    Storage::fake('public');

    $user = User::factory()->create();
    $this->actingAs($user);

    $response = Volt::test('settings.profile')
        ->set('name', 'Updated Profile')
        ->set('email', 'updated@example.com')
        ->set('phone', '+2348099999999')
        ->set('bio', 'Profile details for the resident portal.')
        ->set('avatar', UploadedFile::fake()->image('avatar.jpg'))
        ->call('updateProfileInformation');

    $response->assertHasNoErrors();

    $user->refresh();

    expect($user->name)->toBe('Updated Profile');
    expect($user->phone)->toBe('+2348099999999');
    expect($user->bio)->toBe('Profile details for the resident portal.');
    expect($user->avatar_path)->not->toBeNull();
    Storage::disk('public')->assertExists($user->avatar_path);
});
