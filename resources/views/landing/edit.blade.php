@extends('layouts.app')
@section('title', 'Лендинг · ВелоУчёт')
@section('heading', 'Лендинг')
@section('content')
<form class="panel form" method="post" action="{{ route('landing.update') }}">
    @csrf
    @method('put')
    <h2>Основной экран</h2>
    <div class="form-grid">
        <label>Название бренда<input required name="brand_name" value="{{ old('brand_name', $settings->brand_name) }}"></label>
        <label>Кнопка действия<input required name="primary_cta" value="{{ old('primary_cta', $settings->primary_cta) }}"></label>
        <label class="full">Заголовок<input required name="hero_title" value="{{ old('hero_title', $settings->hero_title) }}"></label>
        <label class="full">Описание<textarea name="hero_subtitle">{{ old('hero_subtitle', $settings->hero_subtitle) }}</textarea></label>
    </div>

    <h2>Контакты и условия</h2>
    <div class="form-grid">
        <label>Телефон<input name="phone" value="{{ old('phone', $settings->phone) }}"></label>
        <label>Telegram или ссылка<input name="telegram" value="{{ old('telegram', $settings->telegram) }}"></label>
        <label class="full">Основной адрес<input name="address" value="{{ old('address', $settings->address) }}"></label>
        <label>График работы<input name="working_hours" value="{{ old('working_hours', $settings->working_hours) }}"></label>
        <label>Цена / заметка<input name="price_note" value="{{ old('price_note', $settings->price_note) }}"></label>
    </div>

    <h2>Преимущества</h2>
    <div class="form-grid">
        <label>Первое<input name="feature_speed" value="{{ old('feature_speed', $settings->feature_speed) }}"></label>
        <label>Второе<input name="feature_service" value="{{ old('feature_service', $settings->feature_service) }}"></label>
        <label>Третье<input name="feature_locations" value="{{ old('feature_locations', $settings->feature_locations) }}"></label>
        <label class="check full"><input type="hidden" name="is_published" value="0"><input type="checkbox" name="is_published" value="1" @checked(old('is_published', $settings->is_published))> Опубликовать лендинг</label>
    </div>

    <div class="form-actions">
        <a class="btn ghost" href="{{ route('landing') }}" target="_blank">Открыть лендинг</a>
        <button class="btn">Сохранить</button>
    </div>
</form>
@endsection
