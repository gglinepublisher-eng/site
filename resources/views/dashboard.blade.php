@extends('layouts.app')
@section('title', 'Обзор · ВелоУчёт')
@section('heading', auth()->user()->isAdmin() ? 'Панель управления' : 'Рабочий обзор')
@section('content')
@php
    $available = $bikes->where('status', 'available')->count();
    $overdue = $activeRentals->filter(fn($r) => $r->state === 'overdue')->count();
    $inService = $bikes->where('status', 'service')->count();
    $maxActivity = max(1, $rentalActivity->max('count'));
    $fleetTotal = max(1, $bikes->count());
@endphp

<section class="welcome-card">
    <div>
        <span class="eyebrow">{{ auth()->user()->isAdmin() ? 'Сводка по всей сети' : 'Сводка по вашей точке' }}</span>
        <h2>{{ $overdue ? 'Есть задачи, которые требуют внимания' : 'Парк работает стабильно' }}</h2>
        <p>{{ $overdue ? "Просрочено аренд: $overdue. Проверьте ближайшие возвраты и свяжитесь с клиентами." : 'Просроченных аренд нет. Можно сосредоточиться на выдачах и плановом сервисе.' }}</p>
    </div>
    <div class="welcome-actions">
        <a class="btn" href="{{ route('rentals.create') }}">Новая аренда</a>
        <a class="btn ghost" href="{{ route('rentals.index') }}">Открыть аренды</a>
    </div>
</section>

<div class="stats">
    <div class="stat accent-green"><div class="stat-top"><span>Велосипедов в парке</span><i>В</i></div><b>{{ $bikes->count() }}</b><small>{{ $available }} свободно для аренды</small></div>
    <div class="stat {{ $overdue ? 'accent-red' : 'accent-blue' }}"><div class="stat-top"><span>Сейчас в аренде</span><i>А</i></div><b>{{ $activeRentals->count() }}</b><small>{{ $overdue ? "$overdue просрочено" : 'Просрочек нет' }}</small></div>
    <div class="stat accent-violet"><div class="stat-top"><span>Выручка за месяц</span><i>₽</i></div><b>{{ number_format($monthRevenue, 0, ',', ' ') }} ₽</b><small>По оформленным арендам</small></div>
    <div class="stat accent-orange"><div class="stat-top"><span>Сервис за месяц</span><i>С</i></div><b>{{ number_format($monthServiceCost, 0, ',', ' ') }} ₽</b><small>{{ $inService }} велосипедов в ремонте</small></div>
</div>

<div class="dashboard-grid">
    <section class="panel chart-panel">
        <div class="panel-head"><div><h2>Динамика аренд</h2><p>Новые выдачи за последние 7 дней</p></div><a href="{{ route('rentals.index') }}">Все аренды</a></div>
        <div class="bar-chart">
            @foreach($rentalActivity as $day)
            <div class="bar-column">
                <strong>{{ $day['count'] }}</strong>
                <div class="bar-track"><i style="height: {{ max(8, $day['count'] / $maxActivity * 100) }}%"></i></div>
                <span>{{ $day['label'] }}</span><small>{{ $day['date'] }}</small>
            </div>
            @endforeach
        </div>
    </section>

    <section class="panel">
        <div class="panel-head"><div><h2>Состояние парка</h2><p>Распределение по статусам</p></div></div>
        <div class="donut-layout">
            <div class="donut" style="--available: {{ $bikes->where('status', 'available')->count() / $fleetTotal * 100 }}%; --rented: {{ $bikes->where('status', 'rented')->count() / $fleetTotal * 100 }}%; --service: {{ $bikes->where('status', 'service')->count() / $fleetTotal * 100 }}%;">
                <div><b>{{ $bikes->count() }}</b><span>всего</span></div>
            </div>
            <div class="status-legend">
                @foreach(['available' => 'Свободны', 'rented' => 'В аренде', 'service' => 'В сервисе', 'retired' => 'Списаны'] as $key => $label)
                <div><i class="{{ $key }}"></i><span>{{ $label }}</span><b>{{ $bikes->where('status', $key)->count() }}</b></div>
                @endforeach
            </div>
        </div>
    </section>
</div>

@if(auth()->user()->isAdmin())
<section class="panel">
    <div class="panel-head"><div><h2>Мониторинг точек</h2><p>Загрузка парка и доступность велосипедов</p></div><a href="{{ route('locations.index') }}">Управление точками</a></div>
    <div class="location-monitor">
        @forelse($locationLoad as $location)
        <div class="location-row">
            <div><strong>{{ $location['name'] }}</strong><small>{{ $location['city'] ?: 'Город не указан' }} · {{ $location['total'] }} велосипедов</small></div>
            <div class="load-meter"><i style="width: {{ $location['load'] }}%"></i></div>
            <div class="location-values"><b>{{ $location['load'] }}%</b><small>{{ $location['available'] }} свободно</small></div>
        </div>
        @empty <div class="empty">Точки пока не заполнены</div> @endforelse
    </div>
</section>
@endif

<div class="dashboard-grid">
    <section class="panel">
        <div class="panel-head"><div><h2>Ближайшие возвраты</h2><p>Активные аренды, которые требуют контроля</p></div><a href="{{ route('rentals.index') }}">Все аренды</a></div>
        @forelse($activeRentals->take(6) as $rental)
            <div class="list-row"><div><strong>{{ $rental->bike->number }} · {{ $rental->bike->model }}</strong><small>{{ $rental->customer?->full_name ?: $rental->renter_name }} · {{ $rental->customer?->phone ?: $rental->renter_phone }}</small></div><div><strong>до {{ $rental->due_at->format('d.m.Y') }}</strong><small>{{ number_format($rental->payment, 0, ',', ' ') }} ₽</small></div><span class="badge {{ $rental->state }}">{{ $rental->state === 'overdue' ? 'Просрочена' : 'Активна' }}</span></div>
        @empty <div class="empty">Нет активных аренд</div> @endforelse
    </section>
    <section class="panel">
        <div class="panel-head"><div><h2>Последние работы</h2><p>Свежие записи из сервисной истории</p></div><a href="{{ route('service.index') }}">Открыть сервис</a></div>
        @forelse($recentService as $record)
            <div class="list-row compact-row"><div><strong>{{ $record->bike_title }}</strong><small>{{ $record->problem }}</small></div><div><strong>{{ number_format($record->cost, 0, ',', ' ') }} ₽</strong><small>{{ $record->serviced_at->format('d.m.Y') }}</small></div></div>
        @empty <div class="empty">Сервисных записей пока нет</div> @endforelse
    </section>
</div>
@endsection
