<?php

namespace Database\Seeders;

use App\Models\Bike;
use App\Models\City;
use App\Models\Location;
use App\Models\Rental;
use App\Models\ServiceRecord;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    public function run(): void
    {
        $city = City::firstOrCreate(['code' => 'EKB'], ['name' => 'Ekaterinburg']);
        $location = Location::firstOrCreate(['city_id' => $city->id, 'code' => 'MAIN'], [
            'name' => 'Main location',
            'address' => 'Set location address',
        ]);

        Bike::whereNull('location_id')->update(['location_id' => $location->id]);
        Rental::whereNull('pickup_location_id')->update(['pickup_location_id' => $location->id]);
        ServiceRecord::whereNull('location_id')->update(['location_id' => $location->id]);
    }
}
