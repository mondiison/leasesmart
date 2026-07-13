<?php

namespace App\Livewire\Marketplace;

use App\Enums\PropertyType;
use App\Models\Property;
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
    public string $type = '';

    #[Url]
    public string $bedrooms = '';

    #[Url(as: 'max_rent')]
    public string $maxRent = '';

    #[Url(as: 'featured', except: false)]
    public bool $featuredOnly = false;

    public function updating(string $name): void
    {
        if (in_array($name, ['search', 'city', 'type', 'bedrooms', 'maxRent', 'featuredOnly'], true)) {
            $this->resetPage();
        }
    }

    public function clearFilters(): void
    {
        $this->search = '';
        $this->city = '';
        $this->type = '';
        $this->bedrooms = '';
        $this->maxRent = '';
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

        return view('livewire.marketplace.index', [
            'properties' => $properties,
            'cities' => $cities,
            'propertyTypes' => PropertyType::cases(),
            'bedroomOptions' => [1, 2, 3, 4, 5],
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

        if ($this->type !== '') {
            $query->where('property_type', $this->type);
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

        if ($this->maxRent !== '') {
            $query->where('rent_amount', '<=', (float) $this->maxRent);
        }

        return $query;
    }
}
