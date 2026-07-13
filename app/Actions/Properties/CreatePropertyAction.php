<?php

namespace App\Actions\Properties;

use App\Actions\Activity\LogActivityAction;
use App\Enums\PropertyPublishStatus;
use App\Models\Property;
use App\Models\User;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class CreatePropertyAction
{
    public function __construct(protected LogActivityAction $logActivity)
    {
    }

    /**
     * @param array<string, mixed> $payload
     */
    public function execute(User $actor, array $payload): Property
    {
        return DB::transaction(function () use ($actor, $payload): Property {
            $property = Property::query()->create([
                'landlord_id' => $payload['landlord_id'] ?? null,
                'caretaker_id' => $payload['caretaker_id'] ?? null,
                'title' => $payload['title'],
                'slug' => $this->uniqueSlug($payload['title']),
                'property_code' => $this->nullableValue($payload['property_code'] ?? null),
                'property_type' => $payload['property_type'],
                'description' => $this->nullableValue($payload['description'] ?? null),
                'address_line_1' => $payload['address_line_1'],
                'address_line_2' => $this->nullableValue($payload['address_line_2'] ?? null),
                'city' => $payload['city'],
                'state' => $this->nullableValue($payload['state'] ?? null),
                'country' => $payload['country'],
                'postal_code' => $this->nullableValue($payload['postal_code'] ?? null),
                'latitude' => $this->nullableValue($payload['latitude'] ?? null),
                'longitude' => $this->nullableValue($payload['longitude'] ?? null),
                'year_built' => $this->nullableValue($payload['year_built'] ?? null),
                'publish_status' => $payload['publish_status'],
                'is_featured' => (bool) ($payload['is_featured'] ?? false),
                'published_at' => $payload['publish_status'] === PropertyPublishStatus::Published->value ? now() : null,
                'created_by' => $actor->getKey(),
                'updated_by' => $actor->getKey(),
            ]);

            $property->amenities()->sync($payload['amenity_ids'] ?? []);
            $this->attachMedia($property, $payload['media'] ?? []);

            $this->logActivity->execute(
                user: $actor,
                action: 'property_created',
                description: "Created property {$property->title}.",
                subject: $property,
                metadata: ['publish_status' => $property->publish_status?->value],
            );

            return $property;
        });
    }

    protected function nullableValue(mixed $value): mixed
    {
        return blank($value) ? null : $value;
    }

    protected function uniqueSlug(string $title): string
    {
        $slug = Str::slug($title);
        $original = $slug !== '' ? $slug : 'property';
        $counter = 1;

        while (Property::query()->where('slug', $slug = $counter === 1 ? $original : $original.'-'.$counter)->exists()) {
            $counter++;
        }

        return $slug;
    }

    /**
     * @param array<int, UploadedFile> $media
     */
    protected function attachMedia(Property $property, array $media): void
    {
        foreach ($media as $file) {
            $property->addMedia($file)->toMediaCollection('gallery');
        }
    }
}
