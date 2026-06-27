<?php

namespace Database\Seeders;

use App\Models\City;
use App\Models\Location;
use App\Models\Bike;
use App\Models\Rental;
use App\Models\ServiceRecord;
use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        $city = City::firstOrCreate(['code' => 'EKB'], ['name' => 'Екатеринбург']);
        $location = Location::firstOrCreate(['city_id' => $city->id, 'code' => 'MAIN'], [
            'name' => 'Основная точка',
            'address' => 'Укажите адрес точки',
        ]);
        Bike::whereNull('location_id')->update(['location_id' => $location->id]);
        Rental::whereNull('pickup_location_id')->update(['pickup_location_id' => $location->id]);
        ServiceRecord::whereNull('location_id')->update(['location_id' => $location->id]);
        User::firstOrCreate(['email' => 'admin@velochet.local'], [
            'name' => 'Администратор',
            'password' => 'admin123',
            'role' => 'admin',
            'is_active' => true,
        ]);
    }
}
