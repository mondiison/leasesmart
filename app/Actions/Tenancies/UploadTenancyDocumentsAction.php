<?php

namespace App\Actions\Tenancies;

use App\Actions\Activity\LogActivityAction;
use App\Models\Tenancy;
use App\Models\User;
use App\Notifications\TenancyDocumentAddedNotification;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;

class UploadTenancyDocumentsAction
{
    public function __construct(protected LogActivityAction $logActivity)
    {
    }

    /**
     * @param array<int, UploadedFile> $documents
     */
    public function execute(User $actor, Tenancy $tenancy, array $documents): Tenancy
    {
        return DB::transaction(function () use ($actor, $tenancy, $documents): Tenancy {
            foreach ($documents as $document) {
                $tenancy
                    ->addMedia($document)
                    ->usingName(pathinfo($document->getClientOriginalName(), PATHINFO_FILENAME))
                    ->toMediaCollection('documents');
            }

            $count = count($documents);

            $this->logActivity->execute(
                user: $actor,
                action: 'tenancy_documents_uploaded',
                description: $count === 1
                    ? "Uploaded 1 document for {$tenancy->tenant_name}."
                    : "Uploaded {$count} documents for {$tenancy->tenant_name}.",
                subject: $tenancy,
                metadata: ['document_count' => $count],
            );

            if ($tenancy->tenantUser && ! $tenancy->tenantUser->is($actor)) {
                $tenancy->tenantUser->notify(new TenancyDocumentAddedNotification($tenancy, $count));
            }

            return $tenancy->fresh(['property', 'unit', 'tenantUser', 'media', 'activityLogs.user']);
        });
    }
}
