@extends('layouts.app')
@section('title', 'Аренды · ВелоУчёт')
@section('heading', 'Аренды')
@section('content')
<form class="toolbar" method="get"><input name="search" value="{{ request('search') }}" placeholder="Клиент, телефон, номер велосипеда..."><select name="location_id"><option value="">Все точки</option>@foreach($locations as $location)<option value="{{ $location->id }}" @selected(request('location_id')==$location->id)>{{ $location->city->name }} · {{ $location->name }}</option>@endforeach</select><button class="btn ghost">Найти</button><a class="btn ghost" href="{{ route('export','rentals') }}">CSV</a></form>
<section class="panel table-wrap"><table><thead><tr><th>Велосипед</th><th>Клиент</th><th>Точка выдачи</th><th>Период</th><th>Оплата</th><th>Статус</th><th></th></tr></thead><tbody>
@forelse($rentals as $rental)<tr>
    <td><strong>{{ $rental->bike->number }} · {{ $rental->bike->model }}</strong></td><td>{{ $rental->customer?->full_name ?: $rental->renter_name }}<small>{{ $rental->customer?->phone ?: $rental->renter_phone }}</small></td>
    <td>{{ $rental->pickupLocation?->city?->name ?: '—' }}<small>{{ $rental->pickupLocation?->name }}</small></td>
    <td>{{ $rental->started_at->format('d.m.Y') }} — {{ $rental->due_at->format('d.m.Y') }}<small>{{ $rental->returned_at ? 'Возвращен '.$rental->returned_at->format('d.m.Y') : 'Не возвращен' }}</small></td>
    <td>{{ number_format($rental->payment,0,',',' ') }} ₽<small>Залог {{ number_format($rental->deposit,0,',',' ') }} ₽</small></td>
    <td><span class="badge {{ $rental->state }}">{{ $rental->state === 'closed' ? 'Завершена' : ($rental->state === 'overdue' ? 'Просрочена' : 'Активна') }}</span></td>
    <td class="row-actions"><a class="small" href="{{ route('rentals.show',$rental) }}">Карточка</a>@if(!$rental->returned_at)<a class="small" href="{{ route('rentals.return.create',$rental) }}">Возврат</a>@endif</td>
</tr>@empty<tr><td class="empty" colspan="7">Аренды не найдены</td></tr>@endforelse
</tbody></table></section>{{ $rentals->links() }}
@endsection
