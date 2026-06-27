@extends('layouts.app')
@section('title', 'Возврат '.$rental->bike->number.' · ВелоУчёт')
@section('heading', 'Возврат '.$rental->bike->number)
@section('content')
<form class="panel form" method="post" enctype="multipart/form-data" action="{{ route('rentals.return.store',$rental) }}">@csrf
<h2>Акт состояния при возврате</h2><div class="form-grid">
    <label>Точка возврата<select required name="return_location_id"><option value="">Выберите точку</option>@foreach($locations as $location)<option value="{{ $location->id }}" @selected(old('return_location_id',$rental->pickup_location_id)==$location->id)>{{ $location->city->name }} · {{ $location->name }}</option>@endforeach</select></label>
    <label>Дата возврата<input required type="date" name="returned_at" value="{{ old('returned_at',today()->format('Y-m-d')) }}"></label>
    <label>Кто принял<input required name="inspected_by" value="{{ old('inspected_by') }}"></label>
    <label>Пробег, км<input required type="number" min="0" name="mileage" value="{{ old('mileage',$rental->bike->mileage) }}"></label>
    <label>Заряд аккумулятора, %<input required type="number" min="0" max="100" name="battery_percent" value="{{ old('battery_percent') }}"></label>
    <label>Стоимость ущерба, ₽<input type="number" min="0" step="0.01" name="damage_cost" value="{{ old('damage_cost',0) }}"></label>
    <label>Возвращено залога, ₽<input type="number" min="0" step="0.01" name="deposit_returned" value="{{ old('deposit_returned',$rental->deposit) }}"></label>
    <label class="full">Общее состояние<textarea name="condition_notes">{{ old('condition_notes') }}</textarea></label>
    <label class="full">Новые дефекты и недочеты<textarea name="defects" placeholder="Если указать дефекты, велосипед автоматически перейдет в статус «В сервисе»">{{ old('defects') }}</textarea></label>
    <label class="full">Фото при возврате<input type="file" name="photos[]" accept="image/*" multiple><small>Снимите те же ракурсы, что были при выдаче, и отдельно новые повреждения.</small></label>
</div><div class="form-actions"><a class="btn ghost" href="{{ route('rentals.show',$rental) }}">Отмена</a><button class="btn">Оформить возврат</button></div></form>
@endsection
