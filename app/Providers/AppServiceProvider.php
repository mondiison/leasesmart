<?php

namespace App\Providers;

use App\Actions\Activity\LogActivityAction;
use App\Enums\ActivityAction;
use App\Models\Inspection;
use App\Models\Invoice;
use App\Models\MaintenanceRequest;
use App\Models\Payment;
use App\Models\Property;
use App\Models\PropertyUnit;
use App\Models\RentalApplication;
use App\Models\Tenancy;
use App\Models\User;
use App\Policies\InspectionPolicy;
use App\Policies\InvoicePolicy;
use App\Policies\MaintenanceRequestPolicy;
use App\Policies\PaymentPolicy;
use App\Policies\PropertyPolicy;
use App\Policies\PropertyUnitPolicy;
use App\Policies\RentalApplicationPolicy;
use App\Policies\RolePolicy;
use App\Policies\TenancyPolicy;
use App\Policies\UserPolicy;
use Illuminate\Auth\Events\Login;
use Illuminate\Auth\Events\Logout;
use Illuminate\Auth\Events\Registered;
use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\ServiceProvider;
use Spatie\Permission\Models\Role;

class AppServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        //
    }

    public function boot(): void
    {
        Gate::policy(User::class, UserPolicy::class);
        Gate::policy(Role::class, RolePolicy::class);
        Gate::policy(Property::class, PropertyPolicy::class);
        Gate::policy(PropertyUnit::class, PropertyUnitPolicy::class);
        Gate::policy(Inspection::class, InspectionPolicy::class);
        Gate::policy(RentalApplication::class, RentalApplicationPolicy::class);
        Gate::policy(Tenancy::class, TenancyPolicy::class);
        Gate::policy(Invoice::class, InvoicePolicy::class);
        Gate::policy(Payment::class, PaymentPolicy::class);
        Gate::policy(MaintenanceRequest::class, MaintenanceRequestPolicy::class);

        RateLimiter::for('api', function (Request $request): Limit {
            return Limit::perMinute(60)->by($request->user()?->getAuthIdentifier() ?: $request->ip());
        });

        RateLimiter::for('auth-api', function (Request $request): Limit {
            return Limit::perMinute(5)->by(strtolower((string) $request->input('email')).'|'.$request->ip());
        });

        Event::listen(Login::class, function (Login $event): void {
            $event->user->forceFill([
                'last_login_at' => now(),
            ])->save();

            app(LogActivityAction::class)->execute(
                user: $event->user,
                action: ActivityAction::SignedIn->value,
                description: 'User signed in.',
            );
        });

        Event::listen(Logout::class, function (Logout $event): void {
            if ($event->user === null) {
                return;
            }

            app(LogActivityAction::class)->execute(
                user: $event->user,
                action: ActivityAction::SignedOut->value,
                description: 'User signed out.',
            );
        });

        Event::listen(Registered::class, function (Registered $event): void {
            app(LogActivityAction::class)->execute(
                user: $event->user,
                action: ActivityAction::Registered->value,
                description: 'User account created.',
            );
        });
    }
}
