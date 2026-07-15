<?php

namespace App\Livewire\Marketplace;

use App\Enums\PropertyType;
use App\Models\Property;
use App\Models\PropertyAmenity;
use Illuminate\Database\Eloquent\Builder;
use Livewire\Attributes\Url;
use Livewire\Component;
use Livewire\WithPagination;

class Index extends Component
{
    use WithPagination;

    #[Url(as: 'q')]
    public string $search = '';

    #[Url]
    public string $city = '';

    #[Url]
    public array $types = [];

    #[Url]
    public string $bedrooms = '';

    #[Url]
    public string $bathrooms = '';

    #[Url(as: 'max_rent')]
    public string $maxRent = '';

    #[Url(as: 'amenities')]
    public array $amenityIds = [];

    #[Url(as: 'featured', except: false)]
    public bool $featuredOnly = false;

    public function updating(string $name): void
    {
        if (in_array($name, ['search', 'city', 'types', 'bedrooms', 'bathrooms', 'maxRent', 'amenityIds', 'featuredOnly'], true)) {
            $this->resetPage();
        }
    }

    public function clearFilters(): void
    {
        $this->search = '';
        $this->city = '';
        $this->types = [];
        $this->bedrooms = '';
        $this->bathrooms = '';
        $this->maxRent = '';
        $this->amenityIds = [];
        $this->featuredOnly = false;
        $this->resetPage();
    }

    public function render()
    {
        $properties = Property::query()
            ->publiclyVisible()
            ->with(['amenities', 'media', 'publicUnits' => fn ($query) => $this->applyUnitFilters($query)->orderBy('rent_amount')])
            ->where(function (Builder $query): void {
                $this->applyPropertyFilters($query);
            })
            ->orderByDesc('is_featured')
            ->orderByDesc('published_at')
            ->paginate(9);

        $cities = Property::query()
            ->publiclyVisible()
            ->orderBy('city')
            ->distinct()
            ->pluck('city')
            ->filter()
            ->values();

        $marketplaceTotals = Property::query()
            ->publiclyVisible()
            ->withCount('publicUnits')
            ->get();

        return view('livewire.marketplace.index', [
            'properties' => $properties,
            'cities' => $cities,
            'marketplaceStats' => [
                'listings' => $marketplaceTotals->count(),
                'cities' => $cities->count(),
                'units' => $marketplaceTotals->sum('public_units_count'),
                'featured' => $marketplaceTotals->where('is_featured', true)->count(),
                'propertyTypes' => $marketplaceTotals->pluck('property_type')->unique()->count(),
                'amenities' => PropertyAmenity::query()
                    ->where(function (Builder $query): void {
                        $query
                            ->whereHas('properties', fn (Builder $propertyQuery) => $propertyQuery->publiclyVisible())
                            ->orWhereHas('units.property', fn (Builder $propertyQuery) => $propertyQuery->publiclyVisible());
                    })
                    ->count(),
            ],
            'propertyTypes' => PropertyType::cases(),
            'amenities' => PropertyAmenity::query()->orderBy('name')->limit(8)->get(),
            'bedroomOptions' => [1, 2, 3, 4, 5],
            'bathroomOptions' => [1, 2, 3, 4],
            'isHome' => request()->routeIs('home'),
        ])->layout('components.layouts.marketing', [
            'title' => request()->routeIs('home') ? 'LeaseSmart Premium' : 'Property Listings | LeaseSmart Premium',
        ]);
    }

    protected function applyPropertyFilters(Builder $query): void
    {
        if ($this->featuredOnly) {
            $query->where('is_featured', true);
        }

        if ($this->city !== '') {
            $query->where('city', $this->city);
        }

        $types = array_values(array_filter($this->types));

        if ($types !== []) {
            $query->whereIn('property_type', $types);
        }

        $amenityIds = collect($this->amenityIds)
            ->filter()
            ->map(fn ($amenityId) => (int) $amenityId)
            ->values()
            ->all();

        if ($amenityIds !== []) {
            $query->where(function (Builder $amenityQuery) use ($amenityIds): void {
                $amenityQuery
                    ->whereHas('amenities', fn (Builder $query) => $query->whereIn('property_amenities.id', $amenityIds))
                    ->orWhereHas('publicUnits.amenities', fn (Builder $query) => $query->whereIn('property_amenities.id', $amenityIds));
            });
        }

        if ($this->search !== '') {
            $search = '%'.$this->search.'%';

            $query->where(function (Builder $searchQuery) use ($search): void {
                $searchQuery
                    ->where('title', 'like', $search)
                    ->orWhere('city', 'like', $search)
                    ->orWhere('state', 'like', $search)
                    ->orWhere('address_line_1', 'like', $search)
                    ->orWhereHas('publicUnits', function (Builder $unitQuery) use ($search): void {
                        $unitQuery
                            ->where('unit_name', 'like', $search)
                            ->orWhere('unit_type', 'like', $search);
                    });
            });
        }

        $query->whereHas('publicUnits', fn (Builder $unitQuery) => $this->applyUnitFilters($unitQuery));
    }

    protected function applyUnitFilters($query)
    {
        $query->publiclyVisible();

        if ($this->bedrooms !== '') {
            $query->where('bedrooms', '>=', (int) $this->bedrooms);
        }

        if ($this->bathrooms !== '') {
            $query->where('bathrooms', '>=', (int) $this->bathrooms);
        }

        if ($this->maxRent !== '') {
            $query->where('rent_amount', '<=', (float) $this->maxRent);
        }

        return $query;
    }
}
