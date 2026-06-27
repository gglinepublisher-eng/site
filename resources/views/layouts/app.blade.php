<!doctype html>
<html lang="ru">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>@yield('title', 'ВелоУчёт')</title>
    <link rel="stylesheet" href="{{ asset('app.css') }}">
</head>
<body>
<div class="shell">
    <aside class="sidebar">
        <a class="brand" href="{{ route('dashboard') }}"><b>В</b><span><strong>ВелоУчёт</strong><small>Парк и сервис</small></span></a>
        <nav>
            <a class="{{ request()->routeIs('dashboard') ? 'active' : '' }}" href="{{ route('dashboard') }}"><i>⌂</i><span>Обзор</span></a>
            <a class="{{ request()->routeIs('bikes.*') ? 'active' : '' }}" href="{{ route('bikes.index') }}"><i>В</i><span>Велосипеды</span></a>
            <a class="{{ request()->routeIs('rentals.*') ? 'active' : '' }}" href="{{ route('rentals.index') }}"><i>А</i><span>Аренды</span></a>
            <a class="{{ request()->routeIs('customers.*') ? 'active' : '' }}" href="{{ route('customers.index') }}"><i>К</i><span>Клиенты</span></a>
            <a class="{{ request()->routeIs('service.*') ? 'active' : '' }}" href="{{ route('service.index') }}"><i>С</i><span>Сервис</span></a>
            @if(auth()->user()->isAdmin())
            <div class="nav-label">Управление</div>
            <a class="{{ request()->routeIs('locations.*') ? 'active' : '' }}" href="{{ route('locations.index') }}"><i>Т</i><span>Города и точки</span></a>
            <a class="{{ request()->routeIs('landing.edit') ? 'active' : '' }}" href="{{ route('landing.edit') }}"><i>Л</i><span>Лендинг</span></a>
            <a class="{{ request()->routeIs('users.*') ? 'active' : '' }}" href="{{ route('users.index') }}"><i>У</i><span>Учётные записи</span></a>
            @endif
        </nav>
        <div class="sidebar-bottom">
            <div class="user-avatar">{{ mb_substr(auth()->user()->name, 0, 1) }}</div>
            <div><strong>{{ auth()->user()->name }}</strong><small>{{ auth()->user()->isAdmin() ? 'Все точки' : auth()->user()->location?->name }}</small></div>
            <form method="post" action="{{ route('logout') }}">@csrf<button class="logout-button">Выйти</button></form>
        </div>
    </aside>
    <main>
        <header class="topbar">
            <div><p>{{ now()->translatedFormat('l, j F') }}</p><h1>@yield('heading', 'ВелоУчёт')</h1></div>
            <div class="actions"><a class="btn ghost" href="{{ route('service.create') }}">Запись в сервис</a><a class="btn" href="{{ route('bikes.create') }}">Добавить велосипед</a></div>
        </header>
        @if(session('success'))<div class="flash">{{ session('success') }}</div>@endif
        @if($errors->any())<div class="errors"><strong>Проверьте форму:</strong><ul>@foreach($errors->all() as $error)<li>{{ $error }}</li>@endforeach</ul></div>@endif
        @yield('content')
    </main>
</div>
<script>
document.querySelectorAll('[data-confirm]').forEach(el => el.addEventListener('click', e => {
    if (!confirm(el.dataset.confirm)) e.preventDefault();
}));
document.querySelectorAll('[data-ownership]').forEach(select => {
    const sync = () => {
        document.querySelectorAll('[data-own]').forEach(el => el.hidden = select.value !== 'own');
        document.querySelectorAll('[data-external]').forEach(el => el.hidden = select.value !== 'external');
    };
    select.addEventListener('change', sync); sync();
});
document.querySelectorAll('[data-role]').forEach(select => {
    const sync = () => document.querySelectorAll('[data-location-field]').forEach(el => el.hidden = select.value === 'admin');
    select.addEventListener('change', sync); sync();
});
</script>
</body>
</html>
