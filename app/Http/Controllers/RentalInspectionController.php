<?php

namespace App\Http\Controllers;

use App\Models\Location;
use App\Models\Rental;
use App\Services\PhotoStorage;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Support\AccessScope;

class RentalInspectionController extends Controller
{
    public function createReturn(Rental $rental)
    {
        $this->authorizeRental($rental);
        abort_if($rental->returned_at, 404);
        $rental->load(['bike', 'customer', 'handoverInspection.photos']);
        return view('rentals.return', [
            'rental' => $rental,
            'locations' => Location::with('city')->where('is_active', true)->orderBy('name')->get(),
        ]);
    }

    public function storeReturn(Request $request, Rental $rental, PhotoStorage $photos)
    {
        $this->authorizeRental($rental);
        abort_if($rental->returned_at, 422);
        $data = $request->validate([
            'return_location_id' => ['required', 'exists:locations,id'],
            'returned_at' => ['required', 'date', 'after_or_equal:'.$rental->started_at->format('Y-m-d')],
            'inspected_by' => ['required', 'string', 'max:255'],
            'mileage' => ['required', 'integer', 'min:0'],
            'battery_percent' => ['required', 'integer', 'min:0', 'max:100'],
            'condition_notes' => ['nullable', 'string'],
            'defects' => ['nullable', 'string'],
            'damage_cost' => ['nullable', 'numeric', 'min:0'],
            'deposit_returned' => ['nullable', 'numeric', 'min:0'],
            'photos.*' => ['image', 'max:10240'],
        ]);
        $data['return_location_id'] = AccessScope::locationId() ?: $data['return_location_id'];

        $inspection = DB::transaction(function () use ($data, $rental) {
            $inspection = $rental->inspections()->create([
                'type' => 'return',
                'inspected_by' => $data['inspected_by'],
                'mileage' => $data['mileage'],
                'battery_percent' => $data['battery_percent'],
                'condition_notes' => $data['condition_notes'] ?? null,
                'defects' => $data['defects'] ?? null,
            ]);
            $rental->update([
                'returned_at' => $data['returned_at'],
                'return_location_id' => $data['return_location_id'],
                'damage_cost' => $data['damage_cost'] ?? 0,
                'deposit_returned' => $data['deposit_returned'] ?? 0,
            ]);
            $rental->bike()->update([
                'status' => empty($data['defects']) ? 'available' : 'service',
                'location_id' => $data['return_location_id'],
                'mileage' => $data['mileage'],
                'condition' => empty($data['defects']) ? 'good' : 'attention',
            ]);
            return $inspection;
        });

        $photos->storeMany($inspection, $request->file('photos', []), 'rentals/'.$rental->id.'/return', 'return');
        return to_route('rentals.show', $rental)->with('success', 'Возврат оформлен, акт состояния сохранен');
    }

    private function authorizeRental(Rental $rental): void
    {
        $id = AccessScope::locationId();
        abort_if($id && $rental->pickup_location_id !== $id && $rental->return_location_id !== $id, 403);
    }
}
