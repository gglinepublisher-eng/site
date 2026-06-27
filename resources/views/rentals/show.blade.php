@extends('layouts.app')
@section('title', 'Аренда '.$rental->bike->number.' · ВелоУчёт')
@section('heading', 'Аренда '.$rental->bike->number)
@section('content')
<div class="detail-actions">@if(!$rental->returned_at)<a class="btn" href="{{ route('rentals.return.create',$rental) }}">Оформить возврат</a>@endif<a class="btn ghost" href="{{ route('rentals.edit',$rental) }}">Изменить</a></div>
<section class="panel details">
@foreach(['Велосипед'=>$rental->bike->number.' · '.$rental->bike->model,'Клиент'=>$rental->customer?->full_name ?: $rental->renter_name,'Телефон'=>$rental->customer?->phone ?: $rental->renter_phone,'Выдал'=>$rental->issued_by,'Точка выдачи'=>$rental->pickupLocation?->name ?: '—','Срок'=>$rental->started_at->format('d.m.Y').' — '.$rental->due_at->format('d.m.Y'),'Оплата'=>number_format($rental->payment,0,',',' ').' ₽','Залог'=>number_format($rental->deposit,0,',',' ').' ₽'] as $label=>$value)<div><span>{{ $label }}</span><strong>{{ $value }}</strong></div>@endforeach
</section>
<div class="grid-two">
@foreach(['handoverInspection'=>'Состояние при выдаче','returnInspection'=>'Состояние при возврате'] as $relation=>$title)
@php $inspection=$rental->$relation; @endphp
<section class="panel inspection"><div class="panel-head"><div><h2>{{ $title }}</h2><p>{{ $inspection?->created_at?->format('d.m.Y H:i') ?: 'Акт не оформлен' }}</p></div></div>
@if($inspection)<div class="inspection-data"><p><b>Пробег:</b> {{ number_format($inspection->mileage,0,',',' ') }} км</p><p><b>Заряд:</b> {{ $inspection->battery_percent }}%</p><p><b>Осмотрел:</b> {{ $inspection->inspected_by }}</p><p><b>Состояние:</b> {{ $inspection->condition_notes ?: 'Без замечаний' }}</p><p><b>Дефекты:</b> {{ $inspection->defects ?: 'Не указаны' }}</p></div><div class="photo-grid">@forelse($inspection->photos as $photo)<a href="{{ asset($photo->path) }}" target="_blank"><img src="{{ asset($photo->path) }}" alt="Фото состояния"></a>@empty<span class="empty">Фото не добавлены</span>@endforelse</div>@else<div class="empty">Акт отсутствует</div>@endif
</section>
@endforeach
</div>
@if($rental->returned_at)<section class="panel details">@foreach(['Точка возврата'=>$rental->returnLocation?->name ?: '—','Дата возврата'=>$rental->returned_at->format('d.m.Y'),'Ущерб'=>number_format($rental->damage_cost,0,',',' ').' ₽','Возвращено залога'=>number_format($rental->deposit_returned,0,',',' ').' ₽'] as $label=>$value)<div><span>{{ $label }}</span><strong>{{ $value }}</strong></div>@endforeach</section>@endif
@endsection
