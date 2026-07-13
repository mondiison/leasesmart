<?php

namespace App\Actions\Activity;

use App\Models\ActivityLog;
use App\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\Request;

class LogActivityAction
{
    public function execute(
        ?User $user,
        string $action,
        ?string $description = null,
        ?Model $subject = null,
        array $metadata = [],
        ?Request $request = null,
    ): ActivityLog {
        $request ??= request();

        return ActivityLog::query()->create([
            'user_id' => $user?->getKey(),
            'loggable_type' => $subject?->getMorphClass(),
            'loggable_id' => $subject?->getKey(),
            'action' => $action,
            'description' => $description,
            'metadata' => $metadata === [] ? null : $metadata,
            'ip_address' => $request->ip(),
            'user_agent' => $request->userAgent(),
        ]);
    }
}
