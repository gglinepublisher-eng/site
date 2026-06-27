@extends('layouts.app')
@section('title', 'Города и точки · ВелоУчёт')
@section('heading', 'Города и точки')
@section('content')
<div class="grid-two">
<section class="panel"><div class="panel-head"><div><h2>Города</h2><p>Коды используются в инвентарных номерах</p></div></div>
@foreach($cities as $city)<div class="list-row"><div><strong>{{ $city->name }}</strong><small>Код {{ $city->code }}</small></div><b>{{ $city->locations_count }} точек</b><form method="post" action="{{ route('locations.cities.destroy',$city) }}">@csrf @method('delete')<button class="small" data-confirm="Удалить город?">Удалить</button></form></div>@endforeach
<form class="inline-form" method="post" action="{{ route('locations.cities.store') }}">@csrf<input required name="name" placeholder="Название города"><input required name="code" maxlength="10" placeholder="Код, например EKB"><button class="btn">Добавить</button></form>
</section>
<section class="panel"><div class="panel-head"><div><h2>Точки</h2><p>Выдача, возврат и хранение велосипедов</p></div><a href="{{ route('locations.create') }}">Добавить точку</a></div>
@forelse($locations as $location)<div class="list-row"><div><strong>{{ $location->city->name }} · {{ $location->name }}</strong><small>{{ $location->address ?: 'Адрес не указан' }} · код {{ $location->code }}</small></div><div><strong>{{ $location->bikes_count }} велосипедов</strong><small>{{ $location->is_active ? 'Работает' : 'Отключена' }}</small></div><div class="row-actions"><a class="small" href="{{ route('locations.edit',$location) }}">Изменить</a><form method="post" action="{{ route('locations.destroy',$location) }}">@csrf @method('delete')<button class="small" data-confirm="Удалить точку?">Удалить</button></form></div></div>@empty<div class="empty">Точки не добавлены</div>@endforelse
</section></div>
@endsection
