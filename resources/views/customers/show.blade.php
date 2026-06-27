@extends('layouts.app')
@section('title', $customer->full_name.' · ВелоУчёт')
@section('heading', $customer->full_name)
@section('content')
<div class="detail-actions"><a class="btn ghost" href="{{ route('customers.edit',$customer) }}">Изменить</a></div><section class="panel details">@foreach(['Телефон'=>$customer->phone,'Email'=>$customer->email ?: '—','Документ'=>$customer->document_number ?: '—','Дата рождения'=>$customer->birth_date?->format('d.m.Y') ?: '—','Статус'=>$customer->is_blocked ? 'Заблокирован' : 'Активен'] as $label=>$value)<div><span>{{ $label }}</span><strong>{{ $value }}</strong></div>@endforeach</section>
<section class="panel"><div class="panel-head"><div><h2>История аренд</h2></div></div>@forelse($customer->rentals as $rental)<div class="list-row"><div><strong><a href="{{ route('rentals.show',$rental) }}">{{ $rental->bike->number }} · {{ $rental->bike->model }}</a></strong><small>{{ $rental->started_at->format('d.m.Y') }} — {{ $rental->due_at->format('d.m.Y') }}</small></div><span class="badge {{ $rental->state }}">{{ $rental->state === 'closed' ? 'Завершена' : ($rental->state === 'overdue' ? 'Просрочена' : 'Активна') }}</span></div>@empty<div class="empty">Аренд пока нет</div>@endforelse</section>
@endsection
