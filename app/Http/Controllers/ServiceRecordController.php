<?php

namespace App\Http\Controllers;

use App\Models\Bike;
use App\Models\ServiceRecord;
use App\Models\Location;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use App\Services\PhotoStorage;
use Illuminate\Support\Arr;
use App\Support\AccessScope;

class ServiceRecordController extends Controller
{
    public function index(Request $request)
    {
        $records = AccessScope::service(ServiceRecord::with('bike'))
            ->when($request->search, fn ($q, $search) => $q->where(fn ($q) => $q
                ->where('external_bike', 'like', "%{$search}%")
                ->orWhere('external_owner', 'like', "%{$search}%")
                ->orWhere('problem', 'like', "%{$search}%")
                ->orWhere('work_done', 'like', "%{$search}%")
                ->orWhereHas('bike', fn ($q) => $q->where('number', 'like', "%{$search}%"))))
            ->when($request->ownership, fn ($q, $ownership) => $q->where('ownership', $ownership))
            ->latest('serviced_at')->paginate(20)->withQueryString();

        return view('service.index', compact('records'));
    }

    public function create(Request $request)
    {
        return view('service.form', [
            'serviceRecord' => new ServiceRecord(['ownership' => 'own', 'bike_id' => $request->bike_id, 'location_id' => $request->location_id ?: AccessScope::locationId()]),
            'bikes' => AccessScope::bikes(Bike::query())->orderBy('number')->get(),
            'locations' => Location::with('city')->where('is_active', true)->when(AccessScope::locationId(), fn ($q, $id) => $q->whereKey($id))->orderBy('name')->get(),
        ]);
    }

    public function store(Request $request, PhotoStorage $photos)
    {
        $data = $this->validated($request);
        $data['location_id'] = AccessScope::locationId() ?: ($data['location_id'] ?? null);
        $record = ServiceRecord::create(Arr::except($data, 'photos'));
        $photos->storeMany($record, $request->file('photos', []), 'service/'.$record->id, 'service');
        if ($data['ownership'] === 'own') {
            Bike::whereKey($data['bike_id'])->where('status', '!=', 'retired')->update(['status' => 'service']);
        }
        return to_route('service.index')->with('success', 'Сервисная запись добавлена');
    }

    public function edit(ServiceRecord $serviceRecord)
    {
        $this->authorizeRecord($serviceRecord);
        return view('service.form', compact('serviceRecord') + [
            'bikes' => Bike::orderBy('number')->get(),
            'locations' => Location::with('city')->where('is_active', true)->orderBy('name')->get(),
        ]);
    }

    public function update(Request $request, ServiceRecord $serviceRecord, PhotoStorage $photos)
    {
        $this->authorizeRecord($serviceRecord);
        $data = $this->validated($request);
        $data['location_id'] = AccessScope::locationId() ?: ($data['location_id'] ?? null);
        $serviceRecord->update(Arr::except($data, 'photos'));
        $photos->storeMany($serviceRecord, $request->file('photos', []), 'service/'.$serviceRecord->id, 'service');
        return to_route('service.index')->with('success', 'Сервисная запись обновлена');
    }

    public function destroy(ServiceRecord $serviceRecord)
    {
        $this->authorizeRecord($serviceRecord);
        $serviceRecord->delete();
        return back()->with('success', 'Сервисная запись удалена');
    }

    private function validated(Request $request): array
    {
        $data = $request->validate([
            'ownership' => ['required', Rule::in(['own', 'external'])],
            'location_id' => ['nullable', 'exists:locations,id'],
            'bike_id' => ['nullable', 'required_if:ownership,own', 'exists:bikes,id'],
            'external_bike' => ['nullable', 'required_if:ownership,external', 'string', 'max:255'],
            'external_owner' => ['nullable', 'required_if:ownership,external', 'string', 'max:255'],
            'external_phone' => ['nullable', 'required_if:ownership,external', 'string', 'max:100'],
            'serviced_at' => ['required', 'date'],
            'problem' => ['required', 'string'],
            'work_done' => ['required', 'string'],
            'mechanic' => ['required', 'string', 'max:255'],
            'cost' => ['nullable', 'numeric', 'min:0'],
            'notes' => ['nullable', 'string'],
            'photos.*' => ['image', 'max:10240'],
        ]);

        if ($data['ownership'] === 'own') {
            $data['external_bike'] = $data['external_owner'] = $data['external_phone'] = null;
        } else {
            $data['bike_id'] = null;
        }
        $data['cost'] = $data['cost'] ?? 0;

        return $data;
    }

    private function authorizeRecord(ServiceRecord $record): void
    {
        abort_if(AccessScope::locationId() && $record->location_id !== AccessScope::locationId(), 403);
    }
}
