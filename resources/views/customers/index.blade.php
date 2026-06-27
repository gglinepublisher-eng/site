@extends('layouts.app')
@section('title', 'Клиенты · ВелоУчёт')
@section('heading', 'Клиенты')
@section('content')
<form class="toolbar" method="get"><input name="search" value="{{ request('search') }}" placeholder="Имя или телефон..."><button class="btn ghost">Найти</button><a class="btn" href="{{ route('customers.create') }}">Добавить клиента</a></form>
<section class="panel table-wrap"><table><thead><tr><th>Клиент</th><th>Телефон</th><th>Документ</th><th>Аренд</th><th>Статус</th><th></th></tr></thead><tbody>@forelse($customers as $customer)<tr><td><strong>{{ $customer->full_name }}</strong><small>{{ $customer->email }}</small></td><td>{{ $customer->phone }}</td><td>{{ $customer->document_number ?: '—' }}</td><td>{{ $customer->rentals_count }}</td><td><span class="badge {{ $customer->is_blocked ? 'overdue' : 'available' }}">{{ $customer->is_blocked ? 'Заблокирован' : 'Активен' }}</span></td><td class="row-actions"><a class="small" href="{{ route('customers.show',$customer) }}">Карточка</a><a class="small" href="{{ route('customers.edit',$customer) }}">Изменить</a></td></tr>@empty<tr><td class="empty" colspan="6">Клиенты не найдены</td></tr>@endforelse</tbody></table></section>{{ $customers->links() }}
@endsection
