@extends('layouts.app')
@section('title', 'Сервис · ВелоУчёт')
@section('heading', 'Сервис')
@section('content')
<form class="toolbar" method="get"><input name="search" value="{{ request('search') }}" placeholder="Велосипед, владелец, проблема..."><select name="ownership"><option value="">Все велосипеды</option><option value="own" @selected(request('ownership')==='own')>Наш парк</option><option value="external" @selected(request('ownership')==='external')>Клиентские</option></select><button class="btn ghost">Найти</button><a class="btn ghost" href="{{ route('export','service') }}">CSV для Google Sheets</a><a class="btn" href="{{ route('service.create') }}">Запись в сервис</a></form>
<section class="panel table-wrap"><table><thead><tr><th>Дата</th><th>Велосипед</th><th>Проблема</th><th>Выполненные работы</th><th>Мастер</th><th>Стоимость</th><th></th></tr></thead><tbody>
@forelse($records as $record)<tr>
    <td>{{ $record->serviced_at->format('d.m.Y') }}</td><td><strong>{{ $record->bike_title }}</strong><small>{{ $record->ownership === 'own' ? 'Наш парк' : $record->external_owner.' · '.$record->external_phone }}</small></td>
    <td>{{ $record->problem }}</td><td>{{ $record->work_done }}</td><td>{{ $record->mechanic }}</td><td>{{ number_format($record->cost,0,',',' ') }} ₽</td>
    <td class="row-actions"><a class="small" href="{{ route('service.edit',$record) }}">Изменить</a></td>
</tr>@empty<tr><td class="empty" colspan="7">Сервисные записи не найдены</td></tr>@endforelse
</tbody></table></section>{{ $records->links() }}
@endsection
