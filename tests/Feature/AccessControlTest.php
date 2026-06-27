<?php

namespace Tests\Feature;

use App\Models\Bike;
use App\Models\City;
use App\Models\Customer;
use App\Models\Location;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AccessControlTest extends TestCase
{
    use RefreshDatabase;

    public function test_location_user_cannot_open_admin_settings_or_other_location_bike(): void
    {
        $city = City::create(['name' => 'Город', 'code' => 'CTY']);
        $own = Location::create(['city_id' => $city->id, 'name' => 'Своя', 'code' => 'OWN']);
        $other = Location::create(['city_id' => $city->id, 'name' => 'Чужая', 'code' => 'OTH']);
        $user = User::factory()->create(['role' => 'location', 'location_id' => $own->id]);
        $bike = Bike::create(['number' => 'CTY-OTH-26-0001', 'model' => 'Bike', 'location_id' => $other->id]);

        $this->actingAs($user)->get(route('locations.index'))->assertForbidden();
        $this->actingAs($user)->get(route('bikes.show', $bike))->assertForbidden();
    }

    public function test_guest_is_redirected_to_login(): void
    {
        $this->get(route('dashboard'))->assertRedirect(route('login'));
    }

    public function test_location_user_cannot_delete_bike(): void
    {
        $city = City::create(['name' => 'Город', 'code' => 'CTY']);
        $location = Location::create(['city_id' => $city->id, 'name' => 'Точка', 'code' => 'LOC']);
        $user = User::factory()->create(['role' => 'location', 'location_id' => $location->id]);
        $bike = Bike::create(['number' => 'CTY-LOC-26-0001', 'model' => 'Bike', 'location_id' => $location->id]);

        $this->actingAs($user)->delete('/bikes/'.$bike->id)->assertForbidden();
        $this->assertDatabaseHas('bikes', ['id' => $bike->id]);
    }

    public function test_location_user_cannot_create_rental_with_other_location_bike_or_customer(): void
    {
        $city = City::create(['name' => 'City', 'code' => 'CTY']);
        $own = Location::create(['city_id' => $city->id, 'name' => 'Own', 'code' => 'OWN']);
        $other = Location::create(['city_id' => $city->id, 'name' => 'Other', 'code' => 'OTH']);
        $user = User::factory()->create(['role' => 'location', 'location_id' => $own->id]);
        $ownBike = Bike::create(['number' => 'CTY-OWN-26-0001', 'model' => 'Bike', 'status' => 'available', 'location_id' => $own->id]);
        $otherBike = Bike::create(['number' => 'CTY-OTH-26-0001', 'model' => 'Bike', 'status' => 'available', 'location_id' => $other->id]);
        $otherCustomer = Customer::create(['full_name' => 'Other Customer', 'phone' => '+1', 'location_id' => $other->id]);

        $payload = [
            'bike_id' => $otherBike->id,
            'customer_id' => $otherCustomer->id,
            'issued_by' => 'Manager',
            'pickup_location_id' => $other->id,
            'started_at' => '2026-06-03',
            'due_at' => '2026-06-10',
            'mileage_out' => 10,
            'battery_out' => 100,
        ];

        $this->actingAs($user)->post(route('rentals.store'), $payload)->assertInvalid(['bike_id']);

        $payload['bike_id'] = $ownBike->id;
        $this->actingAs($user)->post(route('rentals.store'), $payload)->assertNotFound();
    }

    public function test_location_user_cannot_create_service_for_other_location_bike(): void
    {
        $city = City::create(['name' => 'City', 'code' => 'CTY']);
        $own = Location::create(['city_id' => $city->id, 'name' => 'Own', 'code' => 'OWN']);
        $other = Location::create(['city_id' => $city->id, 'name' => 'Other', 'code' => 'OTH']);
        $user = User::factory()->create(['role' => 'location', 'location_id' => $own->id]);
        $bike = Bike::create(['number' => 'CTY-OTH-26-0001', 'model' => 'Bike', 'status' => 'available', 'location_id' => $other->id]);

        $this->actingAs($user)->post(route('service.store'), [
            'ownership' => 'own',
            'location_id' => $other->id,
            'bike_id' => $bike->id,
            'serviced_at' => '2026-06-03',
            'problem' => 'Problem',
            'work_done' => 'Work',
            'mechanic' => 'Mechanic',
        ])->assertNotFound();
    }

    public function test_location_user_sees_only_own_location_customers(): void
    {
        $city = City::create(['name' => 'City', 'code' => 'CTY']);
        $own = Location::create(['city_id' => $city->id, 'name' => 'Own', 'code' => 'OWN']);
        $other = Location::create(['city_id' => $city->id, 'name' => 'Other', 'code' => 'OTH']);
        $user = User::factory()->create(['role' => 'location', 'location_id' => $own->id]);
        $ownCustomer = Customer::create(['full_name' => 'Own Customer', 'phone' => '+1', 'location_id' => $own->id]);
        $otherCustomer = Customer::create(['full_name' => 'Other Customer', 'phone' => '+2', 'location_id' => $other->id]);

        $this->actingAs($user)->get(route('customers.index'))
            ->assertOk()
            ->assertSee($ownCustomer->full_name)
            ->assertDontSee($otherCustomer->full_name);

        $this->actingAs($user)->get(route('customers.show', $otherCustomer))->assertForbidden();
    }
}
