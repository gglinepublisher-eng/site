@extends('layouts.app')
@section('title', ($rental->exists ? 'Изменить аренду' : 'Новая аренда').' · ВелоУчёт')
@section('heading', $rental->exists ? 'Изменить аренду' : 'Новая аренда')
@section('content')
<form class="panel form" method="post" enctype="multipart/form-data" action="{{ $rental->exists ? route('rentals.update',$rental) : route('rentals.store') }}">@csrf @if($rental->exists) @method('put') @endif
<h2>Аренда</h2><div class="form-grid">
    <label>Велосипед<select required name="bike_id"><option value="">Выберите велосипед</option>@foreach($bikes as $bike)<option value="{{ $bike->id }}" @selected(old('bike_id',$rental->bike_id)==$bike->id)>{{ $bike->number }} · {{ $bike->model }} · {{ $bike->location?->name }}</option>@endforeach</select></label>
    <label>Точка выдачи<select required name="pickup_location_id"><option value="">Выберите точку</option>@foreach($locations as $location)<option value="{{ $location->id }}" @selected(old('pickup_location_id',$rental->pickup_location_id)==$location->id)>{{ $location->city->name }} · {{ $location->name }}</option>@endforeach</select></label>
    <label>Клиент из базы<select name="customer_id"><option value="">Новый клиент</option>@foreach($customers as $customer)<option value="{{ $customer->id }}" @selected(old('customer_id',$rental->customer_id)==$customer->id)>{{ $customer->full_name }} · {{ $customer->phone }}</option>@endforeach</select></label>
    <label>Кто выдал<input required name="issued_by" value="{{ old('issued_by',$rental->issued_by) }}"></label>
    <label>Имя нового клиента<input name="new_customer_name" value="{{ old('new_customer_name') }}" placeholder="Если клиента нет в списке"></label>
    <label>Телефон нового клиента<input name="new_customer_phone" value="{{ old('new_customer_phone') }}" placeholder="+7 ..."></label>
    <label>Дата выдачи<input required type="date" name="started_at" value="{{ old('started_at',$rental->started_at?->format('Y-m-d') ?: today()->format('Y-m-d')) }}"></label>
    <label>Забрать до<input required type="date" name="due_at" value="{{ old('due_at',$rental->due_at?->format('Y-m-d')) }}"></label>
    <label>Оплата, ₽<input type="number" min="0" step="0.01" name="payment" value="{{ old('payment',$rental->payment) }}"></label>
    <label>Залог, ₽<input type="number" min="0" step="0.01" name="deposit" value="{{ old('deposit',$rental->deposit) }}"></label>
</div>
@if(!$rental->exists)
<h2>Акт состояния при выдаче</h2><div class="form-grid">
    <label>Пробег, км<input required type="number" min="0" name="mileage_out" value="{{ old('mileage_out') }}"></label>
    <label>Заряд аккумулятора, %<input required type="number" min="0" max="100" name="battery_out" value="{{ old('battery_out') }}"></label>
    <label class="full">Общее состояние<textarea name="condition_out" placeholder="Чистота, работа тормозов, свет, комплектность">{{ old('condition_out') }}</textarea></label>
    <label class="full">Имеющиеся дефекты<textarea name="defects_out" placeholder="Царапины, потертости и другие недочеты до аренды">{{ old('defects_out') }}</textarea></label>
    <label class="full">Фото при выдаче<input type="file" name="photos[]" accept="image/*" multiple><small>Сделайте фото с разных сторон, чтобы зафиксировать состояние.</small></label>
</div>
@endif
<div class="form-grid"><label class="full">Примечания<textarea name="notes">{{ old('notes',$rental->notes) }}</textarea></label></div>
<div class="form-actions"><a class="btn ghost" href="{{ route('rentals.index') }}">Отмена</a><button class="btn">Сохранить</button></div></form>
@endsection
