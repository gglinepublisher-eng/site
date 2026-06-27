<?php

namespace App\Http\Controllers;

use App\Models\Bike;
use App\Models\Location;
use App\Services\InventoryNumberGenerator;
use App\Services\PhotoStorage;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\Support\Arr;
use App\Support\AccessScope;

class BikeController extends Controller
{
    public function index(Request $request)
    {
        $bikes = AccessScope::bikes(Bike::with('location.city'))
            ->when($request->search, fn ($q, $search) => $q->where(fn ($q) => $q
                ->where('number', 'like', "%{$search}%")->orWhere('model', 'like', "%{$search}%")
                ->orWhere('serial_number', 'like', "%{$search}%")->orWhere('frame_number', 'like', "%{$search}%")))
            ->when($request->status, fn ($q, $status) => $q->where('status', $status))
            ->when($request->location_id, fn ($q, $location) => $q->where('location_id', $location))
            ->orderBy('number')->paginate(20)->withQueryString();

        $tree = AccessScope::bikes(Bike::with('location.city')->orderBy('number'))->get()->groupBy(fn ($bike) => $bike->location?->city?->name ?: 'Без города')->map(fn ($items) => $items->groupBy(fn ($bike) => $bike->location?->name ?: 'Без точки'));
        return view('bikes.index', ['bikes' => $bikes, 'locations' => $this->locations(), 'tree' => $tree]);
    }

    public function create()
    {
        return view('bikes.form', ['bike' => new Bike(), 'locations' => $this->locations()]);
    }

    public function store(Request $request, InventoryNumberGenerator $generator, PhotoStorage $photos)
    {
        $data = $this->validated($request);
        $data['location_id'] = AccessScope::locationId() ?: $data['location_id'];
        $location = Location::with('city')->findOrFail($data['location_id']);
        $data['number'] = $generator->generate($location);
        $bike = Bike::create(Arr::except($data, 'photos'));
        $photos->storeMany($bike, $request->file('photos', []), 'bikes/'.$bike->id.'/initial', 'initial');

        return to_route('bikes.show', $bike)->with('success', 'Велосипед добавлен. Инвентарный номер: '.$bike->number);
    }

    public function show(Bike $bike)
    {
        $this->authorizeBike($bike);
        $bike->load([
            'location.city', 'photos',
            'rentals' => fn ($q) => $q->with(['customer', 'handoverInspection.photos', 'returnInspection.photos'])->latest('started_at'),
            'serviceRecords' => fn ($q) => $q->latest('serviced_at'),
        ]);
        return view('bikes.show', compact('bike'));
    }

    public function edit(Bike $bike)
    {
        $this->authorizeBike($bike);
        return view('bikes.form', ['bike' => $bike, 'locations' => $this->locations()]);
    }

    public function update(Request $request, Bike $bike, PhotoStorage $photos)
    {
        $this->authorizeBike($bike);
        $data = $this->validated($request, $bike);
        $data['location_id'] = AccessScope::locationId() ?: $data['location_id'];
        $bike->update(Arr::except($data, 'photos'));
        $photos->storeMany($bike, $request->file('photos', []), 'bikes/'.$bike->id.'/initial', 'initial');
        return to_route('bikes.show', $bike)->with('success', 'Данные велосипеда обновлены');
    }

    public function destroy(Bike $bike)
    {
        $this->authorizeBike($bike);
        if ($bike->rentals()->exists() || $bike->serviceRecords()->exists()) {
            return back()->withErrors(['Велосипед нельзя удалить, потому что у него есть история аренд или ремонтов. Переведите его в статус «Списан».']);
        }
        $bike->delete();
        return to_route('bikes.index')->with('success', 'Велосипед удален');
    }

    private function locations()
    {
        return Location::with('city')->where('is_active', true)->when(AccessScope::locationId(), fn ($q, $id) => $q->whereKey($id))->orderBy('name')->get();
    }

    private function authorizeBike(Bike $bike): void
    {
        abort_if(AccessScope::locationId() && $bike->location_id !== AccessScope::locationId(), 403);
    }

    private function validated(Request $request, ?Bike $bike = null): array
    {
        $data = $request->validate([
            'location_id' => ['required', 'exists:locations,id'],
            'model' => ['required', 'string', 'max:255'],
            'serial_number' => ['nullable', 'string', 'max:255'],
            'frame_number' => ['nullable', 'string', 'max:255'],
            'color' => ['nullable', 'string', 'max:100'],
            'manufacture_year' => ['nullable', 'integer', 'min:2000', 'max:'.(now()->year + 1)],
            'motor' => ['nullable', 'string', 'max:255'],
            'battery' => ['nullable', 'string', 'max:255'],
            'mileage' => ['nullable', 'integer', 'min:0'],
            'condition' => ['required', Rule::in(['new', 'good', 'attention', 'critical'])],
            'received_at' => ['nullable', 'date'],
            'commissioned_at' => ['nullable', 'date'],
            'warranty_until' => ['nullable', 'date'],
            'purchase_cost' => ['nullable', 'numeric', 'min:0'],
            'depreciation_cost' => ['nullable', 'numeric', 'min:0'],
            'remaining_payment' => ['nullable', 'numeric', 'min:0'],
            'status' => ['required', Rule::in(['available', 'rented', 'service', 'retired'])],
            'notes' => ['nullable', 'string'],
            'photos.*' => ['image', 'max:10240'],
        ]);
        foreach (['purchase_cost', 'depreciation_cost', 'remaining_payment', 'mileage'] as $field) {
            $data[$field] = $data[$field] ?? 0;
        }
        return $data;
    }
}
