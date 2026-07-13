<?php

use App\Enums\Role;
use App\Models\User;
use Database\Seeders\RoleAndPermissionSeeder;
use Illuminate\Notifications\DatabaseNotification;
use Illuminate\Support\Str;

function createNotificationFor(User $user, array $data = []): DatabaseNotification
{
    return DatabaseNotification::query()->create([
        'id' => (string) Str::uuid(),
        'type' => 'smart-rent-test',
        'notifiable_type' => $user->getMorphClass(),
        'notifiable_id' => $user->getKey(),
        'data' => [
            'title' => 'Payment submitted',
            'message' => 'Payment PAY-1001 is awaiting verification.',
            'action_url' => route('billing.index'),
            'action_label' => 'Review payment',
            ...$data,
        ],
    ]);
}

test('users can see unread notification counts in the notification center', function () {
    $this->seed(RoleAndPermissionSeeder::class);

    $user = User::factory()->create(['email_verified_at' => now()]);
    $user->assignRole(Role::Admin->value);

    createNotificationFor($user);

    $this->actingAs($user)
        ->get(route('notifications.index'))
        ->assertOk()
        ->assertSee('Notifications')
        ->assertSee('1')
        ->assertSee('unread')
        ->assertSee('Payment submitted')
        ->assertSee('Review payment');
});

test('users can mark all notifications as read', function () {
    $this->seed(RoleAndPermissionSeeder::class);

    $user = User::factory()->create(['email_verified_at' => now()]);
    $user->assignRole(Role::Admin->value);

    createNotificationFor($user);

    $this->actingAs($user)
        ->post(route('notifications.read-all'))
        ->assertRedirect();

    expect($user->fresh()->unreadNotifications()->count())->toBe(0);
});

test('opening a notification marks it read and redirects to its action', function () {
    $this->seed(RoleAndPermissionSeeder::class);

    $user = User::factory()->create(['email_verified_at' => now()]);
    $user->assignRole(Role::Admin->value);

    $notification = createNotificationFor($user);

    $this->actingAs($user)
        ->get(route('notifications.open', $notification))
        ->assertRedirect(route('billing.index'));

    expect($notification->fresh()->read())->toBeTrue();
});
