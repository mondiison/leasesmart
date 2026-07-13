<?php

namespace App\Livewire\Properties;

use App\Models\Property;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Livewire\Component;
use Livewire\WithPagination;

class Index extends Component
{
    use AuthorizesRequests, WithPagination;

    public function mount(): void
    {
        $this->authorize('viewAny', Property::class);
    }

    public function render()
    {
        $query = Property::query()
            ->with(['landlord.user', 'caretaker.user'])
            ->withCount('units')
            ->latest();

        $user = auth()->user();

        if ($user->hasRole('landlord') && $user->landlordProfile) {
            $query->where('landlord_id', $user->landlordProfile->getKey());
        }

        if ($user->hasRole('caretaker') && $user->caretakerProfile) {
            $query->where('caretaker_id', $user->caretakerProfile->getKey());
        }

        return view('livewire.properties.index', [
            'properties' => $query->paginate(12),
        ])->layout('components.layouts.app');
    }
}
