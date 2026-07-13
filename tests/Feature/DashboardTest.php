<?php

use App\Enums\Role;
use App\Models\User;
use Database\Seeders\RoleAndPermissionSeeder;

test('guests are redirected to the login page', function () {
    $response = $this->get('/dashboard');
    $response->assertRedirect('/login');
});

test('authenticated users can visit the dashboard', function () {
    $this->seed(RoleAndPermissionSeeder::class);

    $user = User::factory()->create([
        'email_verified_at' => now(),
    ]);
    $user->assignRole(Role::Admin->value);

    $this->actingAs($user);

    $response = $this->get('/dashboard');
    $response
        ->assertStatus(200)
        ->assertSee('Platform Pulse');
});
