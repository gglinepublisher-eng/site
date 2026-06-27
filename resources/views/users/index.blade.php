@extends('layouts.app')
@section('title','Учетные записи · ВелоУчёт')
@section('heading','Учетные записи')
@section('content')
<div class="detail-actions"><a class="btn" href="{{ route('users.create') }}">Добавить учетную запись</a></div>
<section class="panel table-wrap"><table><thead><tr><th>Пользователь</th><th>Роль</th><th>Точка</th><th>Статус</th><th></th></tr></thead><tbody>@foreach($users as $user)<tr><td><strong>{{ $user->name }}</strong><small>{{ $user->email }}</small></td><td>{{ $user->isAdmin() ? 'Администратор' : 'Сотрудник точки' }}</td><td>{{ $user->location?->city?->name }} {{ $user->location?->name }}</td><td><span class="badge {{ $user->is_active ? 'available' : 'retired' }}">{{ $user->is_active ? 'Активна' : 'Отключена' }}</span></td><td class="row-actions"><a class="small" href="{{ route('users.edit',$user) }}">Изменить</a><form method="post" action="{{ route('users.destroy',$user) }}">@csrf @method('delete')<button class="small" data-confirm="Удалить учетную запись?">Удалить</button></form></td></tr>@endforeach</tbody></table></section>
@endsection
