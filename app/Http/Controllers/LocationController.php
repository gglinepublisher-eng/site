<?php

namespace App\Http\Controllers;

use App\Models\City;
use App\Models\Location;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class LocationController extends Controller
{
    public function index()
    {
        return view('locations.index', [
            'cities' => City::withCount('locations')->orderBy('name')->get(),
            'locations' => Location::with('city')->withCount('bikes')->orderBy('name')->get(),
        ]);
    }

    public function create()
    {
        return view('locations.form', ['location' => new Location(), 'cities' => City::orderBy('name')->get()]);
    }

    public function store(Request $request)
    {
        Location::create($this->validated($request));
        return to_route('locations.index')->with('success', 'Точка добавлена');
    }

    public function edit(Location $location)
    {
        return view('locations.form', ['location' => $location, 'cities' => City::orderBy('name')->get()]);
    }

    public function update(Request $request, Location $location)
    {
        $location->update($this->validated($request, $location));
        return to_route('locations.index')->with('success', 'Точка обновлена');
    }

    public function destroy(Location $location)
    {
        if ($location->bikes()->exists() || $location->users()->exists()) {
            return back()->withErrors(['Точку нельзя удалить, пока к ней привязаны велосипеды или учетные записи.']);
        }
        $location->delete();
        return back()->with('success', 'Точка удалена');
    }

    public function storeCity(Request $request)
    {
        City::create($request->validate([
            'name' => ['required', 'string', 'max:255'],
            'code' => ['required', 'string', 'max:10', 'alpha_num', 'unique:cities,code'],
        ]));
        return back()->with('success', 'Город добавлен');
    }

    public function destroyCity(City $city)
    {
        if ($city->locations()->exists()) {
            return back()->withErrors(['Город нельзя удалить, пока в нем есть точки.']);
        }
        $city->delete();
        return back()->with('success', 'Город удален');
    }

    private function validated(Request $request, ?Location $location = null): array
    {
        return $request->validate([
            'city_id' => ['required', 'exists:cities,id'],
            'name' => ['required', 'string', 'max:255'],
            'code' => ['required', 'string', 'max:10', 'alpha_num', Rule::unique('locations')->where('city_id', $request->city_id)->ignore($location)],
            'address' => ['nullable', 'string', 'max:255'],
            'phone' => ['nullable', 'string', 'max:100'],
            'is_active' => ['boolean'],
        ]);
    }
}
