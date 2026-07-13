<?php

namespace Database\Seeders;

use App\Enums\BillingCycle;
use App\Enums\PropertyPublishStatus;
use App\Enums\PropertyType;
use App\Enums\UnitOccupancyStatus;
use App\Models\Caretaker;
use App\Models\Landlord;
use App\Models\Property;
use App\Models\PropertyAmenity;
use App\Models\PropertyUnit;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class PropertyAmenitySeeder extends Seeder
{
    public function run(): void
    {
        foreach ([
            ['name' => 'Borehole Water', 'description' => 'Dedicated borehole and treated water supply.'],
            ['name' => 'Swimming Pool', 'description' => 'Shared leisure pool for residents.'],
            ['name' => '24/7 Security', 'description' => 'Round-the-clock gate and patrol security.'],
            ['name' => 'Backup Power', 'description' => 'Generator and inverter-backed power support.'],
            ['name' => 'Parking Space', 'description' => 'Reserved on-site resident parking.'],
            ['name' => 'Gym', 'description' => 'Resident fitness room and equipment.'],
            ['name' => 'Elevator', 'description' => 'Lift access for upper-floor units.'],
            ['name' => 'Fitted Kitchen', 'description' => 'Cabinetry, burners, and extractor setup included.'],
            ['name' => 'Wi-Fi Ready', 'description' => 'Broadband-ready cabling and router points.'],
            ['name' => 'CCTV Coverage', 'description' => 'Surveillance coverage across key common areas.'],
            ['name' => 'Children Play Area', 'description' => 'Dedicated outdoor recreation area for children.'],
            ['name' => 'Laundry Room', 'description' => 'Shared laundry area for residents.'],
            ['name' => 'Rooftop Lounge', 'description' => 'Common rooftop relaxation and event area.'],
            ['name' => 'Smart Door Access', 'description' => 'Electronic lock and controlled access support.'],
        ] as $amenity) {
            PropertyAmenity::query()->updateOrCreate(
                ['slug' => Str::slug($amenity['name'])],
                [
                    'name' => $amenity['name'],
                    'description' => $amenity['description'],
                ],
            );
        }
    }
}
