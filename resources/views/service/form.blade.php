@extends('layouts.app')
@section('title', ($serviceRecord->exists ? 'Изменить запись' : 'Запись в сервис').' · ВелоУчёт')
@section('heading', $serviceRecord->exists ? 'Изменить сервисную запись' : 'Запись в сервис')
@section('content')
<form class="panel form" method="post" enctype="multipart/form-data" action="{{ $serviceRecord->exists ? route('service.update',$serviceRecord) : route('service.store') }}">@csrf @if($serviceRecord->exists) @method('put') @endif
<div class="form-grid">
    <label>Чей велосипед<select name="ownership" data-ownership><option value="own" @selected(old('ownership',$serviceRecord->ownership ?: 'own')==='own')>Наш парк</option><option value="external" @selected(old('ownership',$serviceRecord->ownership)==='external')>Клиентский велосипед</option></select></label>
    <label>Дата обращения<input required type="date" name="serviced_at" value="{{ old('serviced_at',$serviceRecord->serviced_at?->format('Y-m-d') ?: today()->format('Y-m-d')) }}"></label>
    <label>Точка сервиса<select name="location_id"><option value="">Не указана</option>@foreach($locations as $location)<option value="{{ $location->id }}" @selected(old('location_id',$serviceRecord->location_id)==$location->id)>{{ $location->city->name }} · {{ $location->name }}</option>@endforeach</select></label>
    <label class="full" data-own>Велосипед<select name="bike_id"><option value="">Выберите велосипед</option>@foreach($bikes as $bike)<option value="{{ $bike->id }}" @selected(old('bike_id',$serviceRecord->bike_id)==$bike->id)>{{ $bike->number }} · {{ $bike->model }}</option>@endforeach</select></label>
    <label data-external>Велосипед клиента<input name="external_bike" value="{{ old('external_bike',$serviceRecord->external_bike) }}" placeholder="Модель, цвет, номер рамы"></label>
    <label data-external>Владелец<input name="external_owner" value="{{ old('external_owner',$serviceRecord->external_owner) }}"></label>
    <label data-external>Телефон<input name="external_phone" value="{{ old('external_phone',$serviceRecord->external_phone) }}"></label>
    <label class="full">Проблема / жалоба<textarea required name="problem">{{ old('problem',$serviceRecord->problem) }}</textarea></label>
    <label class="full">Что сделали<textarea required name="work_done">{{ old('work_done',$serviceRecord->work_done) }}</textarea></label>
    <label>Мастер<input required name="mechanic" value="{{ old('mechanic',$serviceRecord->mechanic) }}"></label>
    <label>Стоимость работ, ₽<input type="number" min="0" step="0.01" name="cost" value="{{ old('cost',$serviceRecord->cost) }}"></label>
    <label class="full">Фото до и после ремонта<input type="file" name="photos[]" accept="image/*" multiple></label>
    <label class="full">Примечания<textarea name="notes">{{ old('notes',$serviceRecord->notes) }}</textarea></label>
</div><div class="form-actions"><a class="btn ghost" href="{{ route('service.index') }}">Отмена</a><button class="btn">Сохранить</button></div></form>
@endsection
