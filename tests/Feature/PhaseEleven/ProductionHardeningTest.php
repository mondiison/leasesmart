<?php

use App\Enums\Role;
use App\Models\User;
use Database\Seeders\RoleAndPermissionSeeder;
use Illuminate\Support\Facades\Artisan;

function phaseElevenUser(string $email = 'hardening@example.com', bool $active = true): User
{
    $user = User::factory()->create([
        'email' => $email,
        'password' => 'password',
        'is_active' => $active,
        'email_verified_at' => now(),
    ]);

    $user->assignRole(Role::Tenant->value);
    $user->tenantProfile()->create([
        'full_name' => $user->name,
        'email' => $user->email,
    ]);

    return $user;
}

test('web responses include baseline security headers', function () {
    $response = $this->get('/');

    $response
        ->assertOk()
        ->assertHeader('X-Frame-Options', 'SAMEORIGIN')
        ->assertHeader('X-Content-Type-Options', 'nosniff')
        ->assertHeader('Referrer-Policy', 'strict-origin-when-cross-origin')
        ->assertHeader('Permissions-Policy', 'camera=(), microphone=(), geolocation=()')
        ->assertHeader('Cross-Origin-Opener-Policy', 'same-origin')
        ->assertHeader('Cross-Origin-Resource-Policy', 'same-site');
});

test('api token endpoint is rate limited', function () {
    $this->seed(RoleAndPermissionSeeder::class);

    $user = phaseElevenUser();

    foreach (range(1, 5) as $attempt) {
        $this->postJson('/api/v1/tokens', [
            'email' => $user->email,
            'password' => 'wrong-password',
            'device_name' => 'Load Test Device',
        ])->assertStatus(422);
    }

    $this->postJson('/api/v1/tokens', [
        'email' => $user->email,
        'password' => 'wrong-password',
        'device_name' => 'Load Test Device',
    ])->assertStatus(429);
});

test('health check command reports readiness signals', function () {
    Artisan::call('app:health-check');

    $output = Artisan::output();

    expect($output)->toContain('database');
    expect($output)->toContain('cache');
    expect($output)->toContain('queue');
    expect($output)->toContain('Health check passed.');
});
