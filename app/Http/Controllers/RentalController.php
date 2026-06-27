<?php

namespace App\Http\Controllers;

use App\Models\Bike;
use App\Models\Customer;
use App\Models\Location;
use App\Models\Rental;
use App\Services\PhotoStorage;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Arr;
use App\Support\AccessScope;
use Illuminate\Validation\ValidationException;

class RentalController extends Controller
{
    public function index(Request $request)
    {
        $rentals = AccessScope::rentals(Rental::with(['bike', 'customer', 'pickupLocation.city']))
            ->when($request->search, fn ($q, $search) => $q->where(fn ($q) => $q
                ->where('renter_name', 'like', "%{$search}%")->orWhere('renter_phone', 'like', "%{$search}%")
                ->orWhereHas('bike', fn ($q) => $q->where('number', 'like', "%{$search}%"))
                ->orWhereHas('customer', fn ($q) => $q->where('full_name', 'like', "%{$search}%")->orWhere('phone', 'like', "%{$search}%"))))
            ->when($request->location_id, fn ($q, $id) => $q->where('pickup_location_id', $id))
            ->latest('started_at')->paginate(20)->withQueryString();

        return view('rentals.index', ['rentals' => $rentals, 'locations' => $this->locations()]);
    }

    public function create(Request $request)
    {
        $rental = new Rental(['bike_id' => $request->bike_id, 'pickup_location_id' => $request->location_id ?: AccessScope::locationId()]);
        return view('rentals.form', $this->formData($rental));
    }

    public function store(Request $request, PhotoStorage $photos)
    {
        $data = $this->validated($request);
        $data['pickup_location_id'] = AccessScope::locationId() ?: $data['pickup_location_id'];
        $this->authorizeBikeChoice((int) $data['bike_id'], (int) $data['pickup_location_id']);
        $customer = $this->customer($data);
        $rental = DB::transaction(function () use ($data, $customer) {
            $rental = Rental::create(Arr::except($data, ['new_customer_name', 'new_customer_phone', 'mileage_out', 'battery_out', 'condition_out', 'defects_out', 'photos']) + [
                'customer_id' => $customer->id,
                'renter_name' => $customer->full_name,
                'renter_phone' => $customer->phone,
            ]);
            $inspection = $rental->inspections()->create([
                'type' => 'handover',
                'inspected_by' => $data['issued_by'],
                'mileage' => $data['mileage_out'] ?? null,
                'battery_percent' => $data['battery_out'] ?? null,
                'condition_notes' => $data['condition_out'] ?? null,
                'defects' => $data['defects_out'] ?? null,
            ]);
            $rental->bike()->update(['status' => 'rented', 'location_id' => $data['pickup_location_id'], 'mileage' => $data['mileage_out'] ?? $rental->bike->mileage]);
            return [$rental, $inspection];
        });
        $photos->storeMany($rental[1], $request->file('photos', []), 'rentals/'.$rental[0]->id.'/handover', 'handover');

        return to_route('rentals.show', $rental[0])->with('success', 'Аренда оформлена, акт выдачи сохранен');
    }

    public function show(Rental $rental)
    {
        $this->authorizeRental($rental);
        $rental->load(['bike.location.city', 'customer', 'pickupLocation.city', 'returnLocation.city', 'handoverInspection.photos', 'returnInspection.photos']);
        return view('rentals.show', compact('rental'));
    }

    public function edit(Rental $rental)
    {
        $this->authorizeRental($rental);
        return view('rentals.form', $this->formData($rental));
    }

    public function update(Request $request, Rental $rental)
    {
        $this->authorizeRental($rental);
        $data = $this->validated($request, false);
        $data['pickup_location_id'] = AccessScope::locationId() ?: $data['pickup_location_id'];
        $this->authorizeBikeChoice((int) $data['bike_id'], (int) $data['pickup_location_id'], $rental);
        $customer = $this->customer($data);
        DB::transaction(function () use ($data, $customer, $rental) {
            $oldBikeId = $rental->bike_id;
            $rental->update(Arr::except($data, ['new_customer_name', 'new_customer_phone', 'mileage_out', 'battery_out', 'condition_out', 'defects_out', 'photos']) + ['customer_id' => $customer->id, 'renter_name' => $customer->full_name, 'renter_phone' => $customer->phone]);
            if ($oldBikeId !== (int) $data['bike_id']) {
                Bike::whereKey($oldBikeId)->update(['status' => 'available']);
            }
            Bike::whereKey($data['bike_id'])->update(['status' => 'rented', 'location_id' => $data['pickup_location_id']]);
        });
        return to_route('rentals.show', $rental)->with('success', 'Аренда обновлена');
    }

    public function destroy(Rental $rental)
    {
        $this->authorizeRental($rental);
        if (! $rental->returned_at) {
            $rental->bike()->update(['status' => 'available']);
        }
        $rental->delete();
        return to_route('rentals.index')->with('success', 'Аренда удалена');
    }

    private function formData(Rental $rental): array
    {
        return [
            'rental' => $rental,
            'bikes' => AccessScope::bikes(Bike::where(fn ($q) => $q->where('status', 'available')->when($rental->bike_id, fn ($q, $id) => $q->orWhere('id', $id)))->with('location.city'))->orderBy('number')->get(),
            'customers' => AccessScope::customers(Customer::where(fn ($q) => $q->where('is_blocked', false)->when($rental->customer_id, fn ($q, $id) => $q->orWhere('id', $id))))->orderBy('full_name')->get(),
            'locations' => $this->locations(),
        ];
    }

    private function locations()
    {
        return Location::with('city')->where('is_active', true)->when(AccessScope::locationId(), fn ($q, $id) => $q->whereKey($id))->orderBy('name')->get();
    }

    private function customer(array $data): Customer
    {
        if (! empty($data['customer_id'])) {
            return AccessScope::customers(Customer::query())->findOrFail($data['customer_id']);
        }

        return Customer::firstOrCreate(
            ['phone' => $data['new_customer_phone'], 'location_id' => $data['pickup_location_id']],
            ['full_name' => $data['new_customer_name']],
        );
    }

    private function validated(Request $request, bool $withInspection = true): array
    {
        $data = $request->validate([
            'bike_id' => ['required', 'exists:bikes,id'],
            'customer_id' => ['nullable', 'exists:customers,id', 'required_without:new_customer_phone'],
            'new_customer_name' => ['nullable', 'string', 'max:255', 'required_without:customer_id'],
            'new_customer_phone' => ['nullable', 'string', 'max:100', 'required_without:customer_id'],
            'issued_by' => ['required', 'string', 'max:255'],
            'pickup_location_id' => ['required', 'exists:locations,id'],
            'started_at' => ['required', 'date'],
            'due_at' => ['required', 'date', 'after_or_equal:started_at'],
            'payment' => ['nullable', 'numeric', 'min:0'],
            'deposit' => ['nullable', 'numeric', 'min:0'],
            'notes' => ['nullable', 'string'],
            'mileage_out' => [$withInspection ? 'required' : 'nullable', 'nullable', 'integer', 'min:0'],
            'battery_out' => [$withInspection ? 'required' : 'nullable', 'nullable', 'integer', 'min:0', 'max:100'],
            'condition_out' => ['nullable', 'string'],
            'defects_out' => ['nullable', 'string'],
            'photos.*' => ['image', 'max:10240'],
        ]);
        $data['payment'] = $data['payment'] ?? 0;
        $data['deposit'] = $data['deposit'] ?? 0;
        return $data;
    }

    private function authorizeRental(Rental $rental): void
    {
        $id = AccessScope::locationId();
        abort_if($id && $rental->pickup_location_id !== $id && $rental->return_location_id !== $id, 403);
    }

    private function authorizeBikeChoice(int $bikeId, int $pickupLocationId, ?Rental $rental = null): void
    {
        $bike = Bike::findOrFail($bikeId);
        $sameRentalBike = $rental && $rental->bike_id === $bike->id;

        if ($bike->location_id !== $pickupLocationId || (! $sameRentalBike && $bike->status !== 'available')) {
            throw ValidationException::withMessages([
                'bike_id' => 'Велосипед должен быть свободен и находиться на выбранной точке.',
            ]);
        }
    }
}
