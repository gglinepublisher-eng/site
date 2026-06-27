<?php

namespace Tests\Feature;

use App\Models\Bike;
use App\Models\City;
use App\Models\Customer;
use App\Models\Location;
use App\Models\Rental;
use App\Services\InventoryNumberGenerator;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class FleetWorkflowTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->actingAs(User::factory()->create(['role' => 'admin']));
    }

    public function test_rental_and_return_update_bike_status(): void
    {
        $city = City::create(['name' => 'Екатеринбург', 'code' => 'EKB']);
        $location = Location::create(['city_id' => $city->id, 'name' => 'Центр', 'code' => 'CEN']);
        $customer = Customer::create(['full_name' => 'Алексей', 'phone' => '+7 900 000-00-00']);
        $bike = Bike::create(['number' => 'EKB-CEN-26-0001', 'model' => 'Test bike', 'status' => 'available', 'location_id' => $location->id]);

        $this->post(route('rentals.store'), [
            'bike_id' => $bike->id,
            'customer_id' => $customer->id,
            'issued_by' => 'Виктор',
            'pickup_location_id' => $location->id,
            'started_at' => '2026-06-03',
            'due_at' => '2026-06-10',
            'mileage_out' => 10,
            'battery_out' => 100,
        ])->assertRedirect();

        $this->assertSame('rented', $bike->fresh()->status);

        $rental = Rental::firstOrFail();
        $this->post(route('rentals.return.store', $rental), [
            'return_location_id' => $location->id,
            'returned_at' => '2026-06-04',
            'inspected_by' => 'Виктор',
            'mileage' => 35,
            'battery_percent' => 50,
            'deposit_returned' => 0,
        ])->assertRedirect();

        $this->assertSame('available', $bike->fresh()->status);
        $this->assertNotNull($rental->fresh()->returned_at);
    }

    public function test_external_bike_can_be_saved_in_service(): void
    {
        $this->post(route('service.store'), [
            'ownership' => 'external',
            'external_bike' => 'Minako, черный',
            'external_owner' => 'Иван',
            'external_phone' => '+7 900 111-22-33',
            'serviced_at' => '2026-06-03',
            'problem' => 'Не работает тормоз',
            'work_done' => 'Заменены колодки',
            'mechanic' => 'Илья',
        ])->assertRedirect(route('service.index'));

        $this->assertDatabaseHas('service_records', [
            'ownership' => 'external',
            'external_owner' => 'Иван',
            'external_bike' => 'Minako, черный',
        ]);
    }

    public function test_inventory_number_is_generated_from_location(): void
    {
        $city = City::create(['name' => 'Екатеринбург', 'code' => 'EKB']);
        $location = Location::create(['city_id' => $city->id, 'name' => 'Центр', 'code' => 'CEN']);

        $number = app(InventoryNumberGenerator::class)->generate($location->load('city'));

        $this->assertSame('EKB-CEN-'.now()->format('y').'-0001', $number);
    }
}
