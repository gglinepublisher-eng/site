@extends('layouts.app')
@section('title', ($bike->exists ? 'Изменить велосипед' : 'Новый велосипед').' · ВелоУчёт')
@section('heading', $bike->exists ? 'Изменить велосипед' : 'Новый велосипед')
@section('content')
<form class="panel form" method="post" enctype="multipart/form-data" action="{{ $bike->exists ? route('bikes.update', $bike) : route('bikes.store') }}">@csrf @if($bike->exists) @method('put') @endif
    <h2>Основные данные</h2><div class="form-grid">
        @if($bike->exists)<label>Инвентарный номер<input value="{{ $bike->number }}" disabled></label>@endif
        <label>Точка хранения<select required name="location_id"><option value="">Выберите точку</option>@foreach($locations as $location)<option value="{{ $location->id }}" @selected(old('location_id',$bike->location_id)==$location->id)>{{ $location->city->name }} · {{ $location->name }}</option>@endforeach</select></label>
        <label>Модель<input required name="model" value="{{ old('model', $bike->model) }}"></label>
        <label>Серийный номер<input name="serial_number" value="{{ old('serial_number', $bike->serial_number) }}"></label>
        <label>Год выпуска<input type="number" min="2000" name="manufacture_year" value="{{ old('manufacture_year', $bike->manufacture_year) }}"></label>
        <label>Статус<select name="status">@foreach(['available'=>'Свободен','rented'=>'В аренде','service'=>'В сервисе','retired'=>'Списан'] as $k=>$v)<option value="{{ $k }}" @selected(old('status', $bike->status ?: 'available')===$k)>{{ $v }}</option>@endforeach</select></label>
        <label>Состояние<select name="condition">@foreach(['new'=>'Новый','good'=>'Хорошее','attention'=>'Требует внимания','critical'=>'Критическое'] as $k=>$v)<option value="{{ $k }}" @selected(old('condition', $bike->condition ?: 'new')===$k)>{{ $v }}</option>@endforeach</select></label>
        <label>Мощность двигателя<input name="motor" value="{{ old('motor', $bike->motor) }}" placeholder="Например, 500 Вт"></label>
        <label>Аккумулятор<input name="battery" value="{{ old('battery', $bike->battery) }}" placeholder="Например, 48V 20Ah"></label>
        <label>Пробег, км<input type="number" min="0" name="mileage" value="{{ old('mileage', $bike->mileage) }}"></label>
    </div><h2>Даты и стоимость</h2><div class="form-grid">
        <label>Дата получения<input type="date" name="received_at" value="{{ old('received_at', $bike->received_at?->format('Y-m-d')) }}"></label>
        <label>Дата ввода в эксплуатацию<input type="date" name="commissioned_at" value="{{ old('commissioned_at', $bike->commissioned_at?->format('Y-m-d')) }}"></label>
        <label>Стоимость покупки, ₽<input type="number" min="0" step="0.01" name="purchase_cost" value="{{ old('purchase_cost', $bike->purchase_cost) }}"></label>
        <label>Амортизационная стоимость, ₽<input type="number" min="0" step="0.01" name="depreciation_cost" value="{{ old('depreciation_cost', $bike->depreciation_cost) }}"></label>
        <label>Осталось платить, ₽<input type="number" min="0" step="0.01" name="remaining_payment" value="{{ old('remaining_payment', $bike->remaining_payment) }}"></label>
        <label>Гарантия до<input type="date" name="warranty_until" value="{{ old('warranty_until', $bike->warranty_until?->format('Y-m-d')) }}"></label>
        <label class="full">Фото состояния при приемке<input type="file" name="photos[]" accept="image/*" multiple><small>Можно выбрать несколько фото. До 10 МБ каждое.</small></label>
        <label class="full">Примечания<textarea name="notes">{{ old('notes', $bike->notes) }}</textarea></label>
    </div><div class="form-actions"><a class="btn ghost" href="{{ route('bikes.index') }}">Отмена</a><button class="btn">Сохранить</button></div>
</form>
@endsection
