<?php

use App\Actions\Billing\GenerateRecurringRentInvoicesAction;
use App\Actions\Reports\SendWeeklyReportDigestsAction;
use App\Actions\Tenancies\SendLeaseExpiryAlertsAction;
use App\Models\User;
use Carbon\CarbonImmutable;
use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;

use Illuminate\Support\Facades\Schedule;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\File;

Schedule::command(
    'queue:work --stop-when-empty --sleep=3 --tries=3 --timeout=60 --max-time=50'
)
->everyMinute()
->withoutOverlapping();

// Schedule::call(function () {
//     Log::info('Scheduler heartbeat ran at '.now());
//     File::append(
//         storage_path('logs/scheduler.log'),
//         'Scheduler heartbeat '.now().PHP_EOL
//     );

// })->everyFifteenSeconds();


Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote')->hourly();

Artisan::command('app:health-check', function () {
    $results = [];

    try {
        DB::connection()->getPdo();
        $results[] = ['check' => 'database', 'status' => 'ok', 'detail' => DB::connection()->getDatabaseName() ?: 'connected'];
    } catch (Throwable $exception) {
        $results[] = ['check' => 'database', 'status' => 'fail', 'detail' => $exception->getMessage()];
    }

    try {
        $key = 'health-check:'.str()->uuid();
        Cache::put($key, 'ok', now()->addMinute());
        $cacheOk = Cache::get($key) === 'ok';
        Cache::forget($key);

        $results[] = ['check' => 'cache', 'status' => $cacheOk ? 'ok' : 'fail', 'detail' => config('cache.default')];
    } catch (Throwable $exception) {
        $results[] = ['check' => 'cache', 'status' => 'fail', 'detail' => $exception->getMessage()];
    }

    $results[] = ['check' => 'queue', 'status' => 'ok', 'detail' => config('queue.default')];
    $results[] = ['check' => 'app_debug', 'status' => config('app.debug') ? 'warn' : 'ok', 'detail' => config('app.debug') ? 'Debug mode is enabled.' : 'Debug mode is disabled.'];
    $results[] = ['check' => 'app_url', 'status' => str(config('app.url'))->startsWith('https://') ? 'ok' : 'warn', 'detail' => config('app.url')];

    $this->table(['Check', 'Status', 'Detail'], $results);

    $hasFailure = collect($results)->contains(fn (array $result): bool => $result['status'] === 'fail');

    if ($hasFailure) {
        $this->error('Health check failed. Review the failing checks above.');

        return self::FAILURE;
    }

    $this->info('Health check passed.');

    return self::SUCCESS;
})->purpose('Run production readiness checks for database, cache, queue, and baseline app config.');

Artisan::command('app:billing-generate-rent-invoices {--actor= : User ID to record as invoice issuer} {--date= : Billing date in Y-m-d format}', function (GenerateRecurringRentInvoicesAction $generateInvoices) {
    $actorId = $this->option('actor');

    $actor = $actorId
        ? User::query()->find($actorId)
        : User::query()->role('admin')->where('is_active', true)->oldest('id')->first();

    if (! $actor) {
        $this->error('No invoice actor found. Pass --actor={user_id} or create an active admin account.');

        return self::FAILURE;
    }

    $billingDate = $this->option('date')
        ? CarbonImmutable::parse($this->option('date'))->startOfDay()
        : CarbonImmutable::today();

    $created = $generateInvoices->execute($actor, $billingDate);

    $this->info("Generated {$created->count()} recurring rent invoice(s).");

    if ($created->isNotEmpty()) {
        $this->table(
            ['Invoice', 'Tenancy', 'Amount', 'Due Date'],
            $created->map(fn ($invoice): array => [
                $invoice->invoice_number,
                $invoice->tenancy_id,
                'NGN '.number_format((float) $invoice->total_amount, 2),
                $invoice->due_date->toDateString(),
            ])->all(),
        );
    }

    return self::SUCCESS;
})->purpose('Generate recurring rent invoices for active tenancy billing periods.');

Artisan::command('app:tenancies-send-expiry-alerts {--actor= : User ID to record as alert initiator} {--date= : Alert date in Y-m-d format}', function (SendLeaseExpiryAlertsAction $sendAlerts) {
    $actorId = $this->option('actor');
    $actor = $actorId ? User::query()->find($actorId) : null;

    $alertDate = $this->option('date')
        ? CarbonImmutable::parse($this->option('date'))->startOfDay()
        : CarbonImmutable::today();

    $sent = $sendAlerts->execute($actor, $alertDate);

    $this->info("Sent {$sent} lease expiry alert batch(es).");

    return self::SUCCESS;
})->purpose('Send lease expiry and renewal reminders for active tenancies.');

Artisan::command('app:reports-send-weekly {--actor= : User ID to record as report sender} {--date= : Report date in Y-m-d format} {--role=all : Recipient role: all, admin, or landlord} {--force : Resend even if this week was already sent}', function (SendWeeklyReportDigestsAction $sendDigests) {
    $role = (string) $this->option('role');

    if (! in_array($role, ['all', 'admin', 'landlord'], true)) {
        $this->error('Invalid role. Use all, admin, or landlord.');

        return self::FAILURE;
    }

    $actorId = $this->option('actor');
    $actor = $actorId ? User::query()->find($actorId) : null;

    $reportDate = $this->option('date')
        ? CarbonImmutable::parse($this->option('date'))->startOfDay()
        : CarbonImmutable::today();

    $sent = $sendDigests->execute($actor, $reportDate, $role, (bool) $this->option('force'));

    $this->info("Sent {$sent} weekly report digest(s).");

    return self::SUCCESS;
})->purpose('Send weekly premium report digest links and export shortcuts to admins and landlords.');
