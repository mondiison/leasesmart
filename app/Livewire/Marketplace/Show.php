<?php

namespace App\Livewire\Marketplace;

use App\Models\Property;
use Illuminate\Support\Facades\URL;
use Livewire\Component;

class Show extends Component
{
    public Property $property;

    public int $activePropertyImage = 0;

    public array $activeUnitImages = [];

    public function mount(Property $property): void
    {
        $property->load([
            'amenities',
            'media',
            'publicUnits' => fn ($query) => $query->with(['amenities', 'media'])->orderBy('rent_amount'),
        ]);

        abort_unless($property->isPubliclyVisible(), 404);

        $this->property = $property;
        $this->activeUnitImages = $property->publicUnits
            ->mapWithKeys(fn ($unit) => [$unit->getKey() => 0])
            ->all();
    }

    public function showPropertyImage(int $index): void
    {
        $this->activePropertyImage = $this->clampedImageIndex($index, $this->property->getMedia('gallery')->count());
    }

    public function previousPropertyImage(): void
    {
        $count = $this->property->getMedia('gallery')->count();

        if ($count < 2) {
            return;
        }

        $this->activePropertyImage = ($this->activePropertyImage - 1 + $count) % $count;
    }

    public function nextPropertyImage(): void
    {
        $count = $this->property->getMedia('gallery')->count();

        if ($count < 2) {
            return;
        }

        $this->activePropertyImage = ($this->activePropertyImage + 1) % $count;
    }

    public function showUnitImage(int $unitId, int $index): void
    {
        $unit = $this->property->publicUnits->firstWhere('id', $unitId);

        if (! $unit) {
            return;
        }

        $this->activeUnitImages[$unitId] = $this->clampedImageIndex($index, $unit->getMedia('gallery')->count());
    }

    public function previousUnitImage(int $unitId): void
    {
        $unit = $this->property->publicUnits->firstWhere('id', $unitId);
        $count = $unit?->getMedia('gallery')->count() ?? 0;

        if ($count < 2) {
            return;
        }

        $current = (int) ($this->activeUnitImages[$unitId] ?? 0);
        $this->activeUnitImages[$unitId] = ($current - 1 + $count) % $count;
    }

    public function nextUnitImage(int $unitId): void
    {
        $unit = $this->property->publicUnits->firstWhere('id', $unitId);
        $count = $unit?->getMedia('gallery')->count() ?? 0;

        if ($count < 2) {
            return;
        }

        $current = (int) ($this->activeUnitImages[$unitId] ?? 0);
        $this->activeUnitImages[$unitId] = ($current + 1) % $count;
    }

    public function render()
    {
        return view('livewire.marketplace.show', [
            'canonicalUrl' => URL::route('marketplace.show', $this->property),
        ])->layout('components.layouts.marketing', [
            'title' => $this->property->title.' | LeaseSmart Premium',
        ]);
    }

    protected function clampedImageIndex(int $index, int $count): int
    {
        if ($count < 1) {
            return 0;
        }

        return max(0, min($index, $count - 1));
    }
}
