@extends('layouts.app')
@section('title', ($location->exists ? 'Изменить точку' : 'Новая точка').' · ВелоУчёт')
@section('heading', $location->exists ? 'Изменить точку' : 'Новая точка')
@section('content')
<form class="panel form" method="post" action="{{ $location->exists ? route('locations.update',$location) : route('locations.store') }}">@csrf @if($location->exists) @method('put') @endif
<div class="form-grid"><label>Город<select required name="city_id"><option value="">Выберите город</option>@foreach($cities as $city)<option value="{{ $city->id }}" @selected(old('city_id',$location->city_id)==$city->id)>{{ $city->name }}</option>@endforeach</select></label><label>Название точки<input required name="name" value="{{ old('name',$location->name) }}"></label><label>Код точки<input required maxlength="10" name="code" value="{{ old('code',$location->code) }}" placeholder="Например, CEN"></label><label>Телефон<input name="phone" value="{{ old('phone',$location->phone) }}"></label><label class="full">Адрес<input name="address" value="{{ old('address',$location->address) }}"></label><label class="check"><input type="hidden" name="is_active" value="0"><input type="checkbox" name="is_active" value="1" @checked(old('is_active',$location->is_active ?? true))> Точка работает</label></div>
<div class="form-actions"><a class="btn ghost" href="{{ route('locations.index') }}">Отмена</a><button class="btn">Сохранить</button></div></form>
@endsection
