<?php

namespace App\Http\Controllers;

use App\Models\Bike;
use App\Models\Rental;
use App\Models\ServiceRecord;
use Symfony\Component\HttpFoundation\StreamedResponse;
use App\Support\AccessScope;

class DashboardController extends Controller
{
    public function __invoke()
    {
        $bikes = AccessScope::bikes(Bike::with('location.city'))->get();
        $rentals = AccessScope::rentals(Rental::with(['bike', 'customer', 'pickupLocation.city']))
            ->latest('started_at')
            ->get();
        $serviceRecords = AccessScope::service(ServiceRecord::with('bike'))
            ->latest('serviced_at')
            ->get();

        $rentalActivity = collect(range(6, 0))->map(function (int $daysAgo) use ($rentals) {
            $date = today()->subDays($daysAgo);

            return [
                'label' => $date->translatedFormat('D'),
                'date' => $date->format('d.m'),
                'count' => $rentals->where('started_at', $date)->count(),
            ];
        });

        $locationLoad = $bikes
            ->groupBy('location_id')
            ->map(function ($locationBikes) {
                $location = $locationBikes->first()->location;
                $total = $locationBikes->count();
                $rented = $locationBikes->where('status', 'rented')->count();

                return [
                    'name' => $location?->name ?: 'Без точки',
                    'city' => $location?->city?->name,
                    'total' => $total,
                    'available' => $locationBikes->where('status', 'available')->count(),
                    'rented' => $rented,
                    'load' => $total ? round($rented / $total * 100) : 0,
                ];
            })
            ->sortByDesc('load')
            ->values();

        return view('dashboard', [
            'bikes' => $bikes,
            'activeRentals' => $rentals->whereNull('returned_at')->sortBy('due_at')->values(),
            'recentService' => $serviceRecords->take(6),
            'rentalActivity' => $rentalActivity,
            'locationLoad' => $locationLoad,
            'monthRevenue' => $rentals->where('started_at', '>=', now()->startOfMonth())->sum('payment'),
            'monthServiceCost' => $serviceRecords->where('serviced_at', '>=', now()->startOfMonth())->sum('cost'),
        ]);
    }

    public function export(string $type): StreamedResponse
    {
        abort_unless(in_array($type, ['bikes', 'rentals', 'service'], true), 404);

        $rows = match ($type) {
            'bikes' => AccessScope::bikes(Bike::with('location.city'))->get()->map(fn (Bike $bike) => [
                $bike->number, $bike->location?->city?->name, $bike->location?->name, $bike->model, $bike->serial_number, $bike->motor, $bike->battery, $bike->mileage,
                optional($bike->received_at)->format('Y-m-d'), optional($bike->commissioned_at)->format('Y-m-d'),
                $bike->purchase_cost, $bike->depreciation_cost, $bike->remaining_payment, $bike->status,
            ]),
            'rentals' => AccessScope::rentals(Rental::with(['bike', 'customer', 'pickupLocation', 'returnLocation']))->get()->map(fn (Rental $rental) => [
                $rental->bike?->number, $rental->customer?->full_name ?: $rental->renter_name, $rental->customer?->phone ?: $rental->renter_phone, $rental->issued_by,
                $rental->pickupLocation?->name, $rental->returnLocation?->name,
                $rental->started_at->format('Y-m-d'), $rental->due_at->format('Y-m-d'),
                optional($rental->returned_at)->format('Y-m-d'), $rental->payment, $rental->deposit, $rental->damage_cost, $rental->deposit_returned, $rental->state,
            ]),
            'service' => AccessScope::service(ServiceRecord::with('bike'))->get()->map(fn (ServiceRecord $record) => [
                $record->serviced_at->format('Y-m-d'), $record->bike_title, $record->ownership,
                $record->external_owner, $record->external_phone, $record->problem, $record->work_done,
                $record->mechanic, $record->cost,
            ]),
        };

        $headers = match ($type) {
            'bikes' => ['Номер', 'Город', 'Точка', 'Модель', 'Серийный номер', 'Двигатель', 'Аккумулятор', 'Пробег', 'Получен', 'Введен в эксплуатацию', 'Стоимость', 'Амортизационная стоимость', 'Осталось платить', 'Статус'],
            'rentals' => ['Велосипед', 'Арендатор', 'Телефон', 'Выдал', 'Точка выдачи', 'Точка возврата', 'Дата выдачи', 'Забрать до', 'Возвращен', 'Оплата', 'Залог', 'Ущерб', 'Возвращено залога', 'Статус'],
            'service' => ['Дата', 'Велосипед', 'Принадлежность', 'Владелец', 'Телефон', 'Проблема', 'Работы', 'Мастер', 'Стоимость'],
        };

        return response()->streamDownload(function () use ($headers, $rows) {
            $out = fopen('php://output', 'w');
            fwrite($out, "\xEF\xBB\xBF");
            fputcsv($out, $headers, ';');
            foreach ($rows as $row) {
                fputcsv($out, $row, ';');
            }
            fclose($out);
        }, $type.'-'.today()->format('Y-m-d').'.csv', ['Content-Type' => 'text/csv; charset=UTF-8']);
    }
}
