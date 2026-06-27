<?php

namespace Tests\Feature;

use App\Models\Bike;
use App\Models\City;
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
}
