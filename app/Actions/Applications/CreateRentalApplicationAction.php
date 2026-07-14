<?php

namespace App\Actions\Applications;

use App\Actions\Activity\LogActivityAction;
use App\Enums\RentalApplicationStatus;
use App\Models\Inspection;
use App\Models\Property;
use App\Models\PropertyUnit;
use App\Models\RentalApplication;
use App\Notifications\RentalApplicationReceivedNotification;
use App\Notifications\RentalApplicationSubmittedNotification;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Notification;

class CreateRentalApplicationAction
{
    public function __construct(protected LogActivityAction $logActivity)
    {
    }

    /**
     * @param array<string, mixed> $payload
     */
    public function execute(Property $property, array $payload): RentalApplication
    {
        return DB::transaction(function () use ($property, $payload): RentalApplication {
            /** @var PropertyUnit $unit */
            $unit = $property->publicUnits()->findOrFail($payload['property_unit_id']);

            $inspection = null;

            if (! empty($payload['inspection_id'])) {
                $inspection = Inspection::query()
                    ->where('property_id', $property->getKey())
                    ->where('property_unit_id', $unit->getKey())
                    ->find($payload['inspection_id']);
            }

            $application = RentalApplication::query()->create([
                'property_id' => $property->getKey(),
                'property_unit_id' => $unit->getKey(),
                'inspection_id' => $inspection?->getKey(),
                'applicant_user_id' => auth()->id(),
                'reviewed_by' => null,
                'status' => RentalApplicationStatus::Submitted,
                'source' => 'marketplace',
                'applicant_name' => $payload['applicant_name'],
                'applicant_email' => $payload['applicant_email'],
                'applicant_phone' => $payload['applicant_phone'],
                'employment_status' => $this->optionalString($payload['employment_status'] ?? null),
                'employer_name' => $this->optionalString($payload['employer_name'] ?? null),
                'monthly_income' => $this->optionalDecimal($payload['monthly_income'] ?? null),
                'preferred_move_in_date' => $this->optionalString($payload['preferred_move_in_date'] ?? null),
                'message' => $this->optionalString($payload['message'] ?? null),
                'submitted_at' => now(),
            ]);

            $this->attachDocuments($application, $payload['documents'] ?? []);

            $this->logActivity->execute(
                user: auth()->user(),
                action: 'rental_application_submitted',
                description: "Rental application submitted for {$property->title}".($unit->unit_name ? " ({$unit->unit_name})" : '.'),
                subject: $application,
                metadata: [
                    'property_id' => $property->getKey(),
                    'property_unit_id' => $unit->getKey(),
                    'inspection_id' => $inspection?->getKey(),
                ],
            );

            Notification::send($this->recipientsFor($property), new RentalApplicationSubmittedNotification($application));
            Notification::route('mail', $application->applicant_email)
                ->notify(new RentalApplicationReceivedNotification($application));

            return $application;
        });
    }

    /**
     * @param array<int, UploadedFile> $documents
     */
    protected function attachDocuments(RentalApplication $application, array $documents): void
    {
        foreach ($documents as $document) {
            $application->addMedia($document)->toMediaCollection('documents');
        }
    }

    /**
     * @return Collection<int, mixed>
     */
    protected function recipientsFor(Property $property): Collection
    {
        return collect([
            $property->landlord?->user,
            ...\App\Models\User::role('admin')->get(),
        ])->filter(fn ($user) => $user !== null)->unique('id')->values();
    }

    protected function optionalString(mixed $value): ?string
    {
        if ($value === null) {
            return null;
        }

        $value = trim((string) $value);

        return $value === '' ? null : $value;
    }

    protected function optionalDecimal(mixed $value): ?float
    {
        if ($value === null || trim((string) $value) === '') {
            return null;
        }

        return (float) $value;
    }
}
