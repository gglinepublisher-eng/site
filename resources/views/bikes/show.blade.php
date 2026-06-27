@extends('layouts.app')
@section('title', $bike->number.' · ВелоУчёт')
@section('heading', $bike->number.' · '.$bike->model)
@section('content')
<div class="detail-actions">
    @if($bike->status === 'available')<a class="btn" href="{{ route('rentals.create',['bike_id'=>$bike->id,'location_id'=>$bike->location_id]) }}">Сдать в аренду</a>@endif
    <a class="btn ghost" href="{{ route('service.create',['bike_id'=>$bike->id,'location_id'=>$bike->location_id]) }}">Отправить в сервис</a>
    <a class="btn ghost" href="{{ route('bikes.edit',$bike) }}">Изменить</a>
    @if(auth()->user()->isAdmin())<form method="post" action="{{ route('bikes.destroy',$bike) }}">@csrf @method('delete')<button class="btn danger-button" data-confirm="Удалить велосипед? Это действие нельзя отменить.">Удалить</button></form>@endif
</div>
<section class="panel details">
@foreach(['Статус'=>['available'=>'Свободен','rented'=>'В аренде','service'=>'В сервисе','retired'=>'Списан'][$bike->status], 'Точка'=>$bike->location?->city?->name.' · '.$bike->location?->name, 'Серийный номер'=>$bike->serial_number ?: '—', 'Двигатель'=>$bike->motor ?: '—', 'Аккумулятор'=>$bike->battery ?: '—', 'Пробег'=>number_format($bike->mileage,0,',',' ').' км', 'Стоимость'=>number_format($bike->purchase_cost,0,',',' ').' ₽', 'Осталось платить'=>number_format($bike->remaining_payment,0,',',' ').' ₽'] as $label=>$value)
<div><span>{{ $label }}</span><strong>{{ $value }}</strong></div>@endforeach
</section>
<section class="panel"><div class="panel-head"><div><h2>Фото при приемке</h2></div></div><div class="photo-grid">@forelse($bike->photos as $photo)<a href="{{ asset($photo->path) }}" target="_blank"><img src="{{ asset($photo->path) }}" alt="Фото велосипеда"></a>@empty<span class="empty">Фото не добавлены</span>@endforelse</div></section>
<section class="panel"><div class="panel-head"><div><h2>Ремонты</h2></div></div>@forelse($bike->serviceRecords as $record)<div class="list-row"><div><strong>{{ $record->serviced_at->format('d.m.Y') }} · {{ $record->problem }}</strong><small>{{ $record->work_done }}</small></div><div><strong>{{ $record->mechanic }}</strong><small>{{ number_format($record->cost,0,',',' ') }} ₽</small></div></div>@empty<div class="empty">Ремонтов пока нет</div>@endforelse</section>
<section class="panel"><div class="panel-head"><div><h2>История аренд</h2></div></div>@forelse($bike->rentals as $rental)<div class="list-row"><div><strong><a href="{{ route('rentals.show',$rental) }}">{{ $rental->customer?->full_name ?: $rental->renter_name }}</a></strong><small>{{ $rental->customer?->phone ?: $rental->renter_phone }}</small></div><div><strong>{{ $rental->started_at->format('d.m.Y') }} — {{ $rental->due_at->format('d.m.Y') }}</strong><small>{{ number_format($rental->payment,0,',',' ') }} ₽</small></div><span class="badge {{ $rental->state }}">{{ $rental->state === 'closed' ? 'Завершена' : ($rental->state === 'overdue' ? 'Просрочена' : 'Активна') }}</span></div>@empty<div class="empty">Аренд пока нет</div>@endforelse</section>
@endsection
