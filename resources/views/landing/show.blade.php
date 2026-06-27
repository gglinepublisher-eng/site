<!doctype html>
<html lang="ru">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ $settings->brand_name }} · аренда самокатов</title>
    <link rel="stylesheet" href="{{ asset('app.css') }}">
</head>
<body class="landing-page wow-landing">
@php
    $phoneLink = $settings->phone ? preg_replace('/[^0-9+]/', '', $settings->phone) : null;
    $availableRatio = $totalBikes ? round(($availableBikes / $totalBikes) * 100) : 0;
    $cities = $locations->pluck('city.name')->filter()->unique()->values();
@endphp

<div class="scroll-progress" aria-hidden="true"><span></span></div>

<header class="landing-nav wow-nav">
    <a class="landing-brand" href="{{ route('landing') }}">{{ $settings->brand_name }}</a>
    <nav>
        <a href="#fleet">Парк</a>
        <a href="#locations">Точки</a>
        <a href="#contacts">Контакты</a>
        @auth
            <a href="{{ route('dashboard') }}">Админка</a>
        @endauth
    </nav>
</header>

<main class="landing-main wow-main">
    <section class="landing-hero wow-hero" data-spotlight>
        <div class="hero-bg" aria-hidden="true"></div>
        <div class="landing-copy hero-copy reveal">
            <span>{{ $settings->price_note ?: 'Аренда каждый день' }}</span>
            <h1>{{ $settings->hero_title }}</h1>
            <p>{{ $settings->hero_subtitle }}</p>
            <div class="landing-actions">
                @if($phoneLink)
                    <a class="landing-btn magnetic" href="#rent-contact" data-rent-trigger>{{ $settings->primary_cta }}</a>
                @endif
                @if($settings->telegram)
                    <a class="landing-btn secondary magnetic" href="{{ $settings->telegram }}" target="_blank" rel="noopener">Написать</a>
                @endif
            </div>
        </div>

        <div class="landing-visual hero-visual reveal" data-depth="0.12">
            <div class="speed-lines" aria-hidden="true"></div>
            @if($heroPhoto)
                <img src="{{ asset($heroPhoto) }}" alt="Самокат для аренды">
            @else
                <div class="scooter-art"><b></b><i></i><em></em></div>
            @endif
            <div class="landing-metric">
                <strong>{{ $availableBikes }}</strong>
                <span>доступно сейчас</span>
            </div>
        </div>
    </section>

    <section id="fleet" class="fleet-band reveal">
        <div>
            <span>Парк в движении</span>
            <h2>{{ $totalBikes }} единиц техники, {{ $availableBikes }} готовы к поездке</h2>
        </div>
        <div class="availability-ring" style="--value: {{ $availableRatio }}%">
            <b>{{ $availableRatio }}%</b>
            <small>свободно</small>
        </div>
    </section>

    <section class="landing-features wow-features">
        <div class="reveal"><strong>{{ $settings->feature_speed }}</strong><span>Оформление без лишнего ожидания на точке выдачи.</span></div>
        <div class="reveal"><strong>{{ $settings->feature_service }}</strong><span>Парк обслуживается в системе учета и регулярно проверяется.</span></div>
        <div class="reveal"><strong>{{ $settings->feature_locations }}</strong><span>Выберите ближайший адрес и уточните наличие перед поездкой.</span></div>
    </section>

    <section id="locations" class="landing-section reveal">
        <div class="landing-section-head">
            <span>Скролл по точкам</span>
            <h2>Где взять самокат</h2>
        </div>
        @if($locations->isNotEmpty())
            <div class="location-filters">
                @if($cities->count() > 1)
                    <div class="city-filter" aria-label="Фильтр по городам">
                        <button type="button" class="active" data-city-filter="all">Все города</button>
                        @foreach($cities as $city)
                            <button type="button" data-city-filter="{{ $city }}">{{ $city }}</button>
                        @endforeach
                    </div>
                @endif
                <label>
                    <span>Точка</span>
                    <select data-location-filter>
                        <option value="all">Все точки</option>
                        @foreach($locations as $location)
                            <option value="{{ $location->name }}">{{ $location->city?->name }} · {{ $location->name }}</option>
                        @endforeach
                    </select>
                </label>
            </div>
        @endif
        <div class="landing-locations location-rail">
            @forelse($locations as $location)
                <article data-city="{{ $location->city?->name }}" data-location="{{ $location->name }}">
                    <strong>{{ $location->name }}</strong>
                    <span>{{ $location->city?->name }}{{ $location->address ? ' · '.$location->address : '' }}</span>
                    <small>{{ $location->phone ?: $settings->phone ?: 'Телефон уточняется' }}</small>
                    <b>{{ $location->available_bikes_count }} свободно</b>
                </article>
            @empty
                <article><strong>Точки скоро появятся</strong><span>Администратор добавит адреса в базе.</span><small>{{ $settings->phone }}</small><b>0 свободно</b></article>
            @endforelse
        </div>
    </section>

    <section id="contacts" class="landing-contact wow-contact reveal">
        <div>
            <span>Контакты</span>
            <h2>Позвоните перед поездкой</h2>
            <p>{{ $settings->working_hours }}</p>
        </div>
        <div>
            @if($phoneLink)<a class="magnetic" href="tel:{{ $phoneLink }}">{{ $settings->phone }}</a>@endif
            <p>{{ $settings->address }}</p>
        </div>
    </section>
</main>

@if($phoneLink)
    <a class="floating-rent magnetic" href="#rent-contact" data-rent-trigger>{{ $settings->primary_cta }}</a>

    <div class="rent-modal" id="rent-contact" aria-hidden="true">
        <div class="rent-modal-backdrop" data-rent-close></div>
        <div class="rent-modal-card" role="dialog" aria-modal="true" aria-labelledby="rent-modal-title">
            <button type="button" class="rent-modal-close" data-rent-close aria-label="Закрыть">×</button>
            <span>Бронирование</span>
            <h2 id="rent-modal-title">Позвоните нам</h2>
            <p>Подскажем свободную точку, наличие техники и условия аренды.</p>
            <strong data-phone-value>{{ $settings->phone }}</strong>
            <div class="rent-modal-actions">
                <a href="tel:{{ $phoneLink }}">Позвонить</a>
                <button type="button" data-copy-phone="{{ $settings->phone }}">Скопировать номер</button>
            </div>
            <small data-copy-status></small>
        </div>
    </div>
@endif

<script>
(() => {
    const root = document.documentElement;
    const progress = document.querySelector('.scroll-progress span');
    const reveals = document.querySelectorAll('.reveal');
    const depthItems = document.querySelectorAll('[data-depth]');
    const cityButtons = document.querySelectorAll('[data-city-filter]');
    const locationSelect = document.querySelector('[data-location-filter]');
    const locationCards = document.querySelectorAll('[data-city][data-location]');
    const modal = document.querySelector('.rent-modal');
    const copyButton = document.querySelector('[data-copy-phone]');
    const copyStatus = document.querySelector('[data-copy-status]');

    const syncScroll = () => {
        const max = root.scrollHeight - innerHeight;
        const ratio = max > 0 ? scrollY / max : 0;
        progress.style.transform = `scaleX(${ratio})`;
        depthItems.forEach((item) => {
            const depth = Number(item.dataset.depth || 0);
            item.style.transform = `translate3d(0, ${scrollY * depth}px, 0)`;
        });
    };

    const observer = new IntersectionObserver((entries) => {
        entries.forEach((entry) => entry.target.classList.toggle('is-visible', entry.isIntersecting));
    }, { threshold: 0.18 });

    reveals.forEach((item) => observer.observe(item));
    addEventListener('scroll', syncScroll, { passive: true });
    syncScroll();

    document.querySelectorAll('.magnetic').forEach((button) => {
        button.addEventListener('pointermove', (event) => {
            const box = button.getBoundingClientRect();
            const x = (event.clientX - box.left - box.width / 2) * 0.18;
            const y = (event.clientY - box.top - box.height / 2) * 0.18;
            button.style.transform = `translate(${x}px, ${y}px)`;
        });
        button.addEventListener('pointerleave', () => {
            button.style.transform = '';
        });
    });

    const filterLocations = () => {
        const activeCity = document.querySelector('[data-city-filter].active')?.dataset.cityFilter || 'all';
        const activeLocation = locationSelect?.value || 'all';
        locationCards.forEach((card) => {
            const cityMatches = activeCity === 'all' || card.dataset.city === activeCity;
            const locationMatches = activeLocation === 'all' || card.dataset.location === activeLocation;
            card.hidden = !(cityMatches && locationMatches);
        });
    };

    cityButtons.forEach((button) => {
        button.addEventListener('click', () => {
            cityButtons.forEach((item) => item.classList.remove('active'));
            button.classList.add('active');
            if (locationSelect) locationSelect.value = 'all';
            filterLocations();
        });
    });

    locationSelect?.addEventListener('change', filterLocations);

    const openModal = () => {
        modal?.classList.add('is-open');
        modal?.setAttribute('aria-hidden', 'false');
        document.body.classList.add('modal-open');
    };

    const closeModal = () => {
        modal?.classList.remove('is-open');
        modal?.setAttribute('aria-hidden', 'true');
        document.body.classList.remove('modal-open');
        if (copyStatus) copyStatus.textContent = '';
    };

    document.querySelectorAll('[data-rent-trigger]').forEach((trigger) => {
        trigger.addEventListener('click', (event) => {
            event.preventDefault();
            openModal();
        });
    });

    document.querySelectorAll('[data-rent-close]').forEach((trigger) => {
        trigger.addEventListener('click', closeModal);
    });

    addEventListener('keydown', (event) => {
        if (event.key === 'Escape') closeModal();
    });

    copyButton?.addEventListener('click', async () => {
        const phone = copyButton.dataset.copyPhone;
        try {
            await navigator.clipboard.writeText(phone);
            copyStatus.textContent = 'Номер скопирован';
        } catch {
            copyStatus.textContent = phone;
        }
    });
})();
</script>
</body>
</html>
