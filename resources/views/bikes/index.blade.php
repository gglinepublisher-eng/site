@extends('layouts.app')
@section('title','Парк · ВелоУчёт')
@section('heading','Парк велосипедов')
@section('content')
<div class="detail-actions"><a class="btn" href="{{ route('bikes.create') }}">Добавить велосипед</a></div>
@forelse($tree as $city => $locationGroups)
<section class="panel fleet-tree"><div class="panel-head"><div><h2>{{ $city }}</h2><p>{{ $locationGroups->flatten()->count() }} велосипедов</p></div></div>
@foreach($locationGroups as $location => $items)
<details open><summary>{{ $location }} <span>{{ $items->count() }}</span></summary><div class="fleet-grid">
@foreach($items as $bike)<article class="bike-tile"><div><a href="{{ route('bikes.show',$bike) }}"><strong>{{ $bike->number }}</strong></a><small>{{ $bike->model }} · {{ number_format($bike->mileage,0,',',' ') }} км</small></div><span class="badge {{ $bike->status }}">{{ ['available'=>'Свободен','rented'=>'В аренде','service'=>'В сервисе','retired'=>'Списан'][$bike->status] }}</span><div class="tile-actions">@if($bike->status === 'available')<a class="small" href="{{ route('rentals.create',['bike_id'=>$bike->id,'location_id'=>$bike->location_id]) }}">Сдать</a>@endif<a class="small" href="{{ route('service.create',['bike_id'=>$bike->id,'location_id'=>$bike->location_id]) }}">Ремонт</a></div></article>@endforeach
</div></details>
@endforeach</section>
@empty<div class="panel empty">В парке пока нет велосипедов</div>@endforelse
@endsection
