<?php

namespace App\Http\Requests\Properties;

use App\Enums\PropertyPublishStatus;
use App\Models\Property;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdatePropertyPublishStatusRequest extends FormRequest
{
    public function authorize(): bool
    {
        /** @var Property $property */
        $property = $this->route('property');

        return $this->user()?->can('publish', $property) ?? false;
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'publish_status' => ['required', Rule::enum(PropertyPublishStatus::class)],
        ];
    }
}
