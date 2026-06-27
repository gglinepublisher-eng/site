@extends('layouts.app')
@section('title', ($customer->exists ? 'Изменить клиента' : 'Новый клиент').' · ВелоУчёт')
@section('heading', $customer->exists ? 'Изменить клиента' : 'Новый клиент')
@section('content')
<form class="panel form" method="post" action="{{ $customer->exists ? route('customers.update',$customer) : route('customers.store') }}">@csrf @if($customer->exists) @method('put') @endif
<div class="form-grid"><label>ФИО<input required name="full_name" value="{{ old('full_name',$customer->full_name) }}"></label><label>Телефон<input required name="phone" value="{{ old('phone',$customer->phone) }}"></label><label>Email<input type="email" name="email" value="{{ old('email',$customer->email) }}"></label><label>Документ<input name="document_number" value="{{ old('document_number',$customer->document_number) }}" placeholder="Серия и номер"></label><label>Дата рождения<input type="date" name="birth_date" value="{{ old('birth_date',$customer->birth_date?->format('Y-m-d')) }}"></label><label class="check"><input type="hidden" name="is_blocked" value="0"><input type="checkbox" name="is_blocked" value="1" @checked(old('is_blocked',$customer->is_blocked))> Заблокирован</label><label class="full">Примечания<textarea name="notes">{{ old('notes',$customer->notes) }}</textarea></label></div>
<div class="form-actions"><a class="btn ghost" href="{{ route('customers.index') }}">Отмена</a><button class="btn">Сохранить</button></div></form>
@endsection
