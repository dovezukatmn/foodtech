@extends('adminlte::page')

@section('title', 'Заказы')

@section('content_header')
    <div class="d-flex justify-content-between">
        <h1>Все заказы</h1>
        <a href="{{ route('admin.orders.kanban') }}" class="btn btn-primary">
            <i class="fas fa-columns"></i> Канбан-доска
        </a>
    </div>
@stop

@section('content')
    <div class="card">
        <div class="card-body p-0">
            <table class="table table-striped">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Клиент</th>
                        <th>Телефон</th>
                        <th>Сумма</th>
                        <th>Статус</th>
                        <th>Дата</th>
                        <th>Действия</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($orders as $order)
                        <tr>
                            <td>{{ $order->id }}</td>
                            <td>{{ $order->user->name ?? 'Гость' }}</td>
                            <td>{{ $order->phone }}</td>
                            <td>{{ number_format($order->total_price, 0, '.', ' ') }} ₽</td>
                            <td>
                                @php
                                    $colors = \App\Models\Order::getStatusColors();
                                    $color = $colors[$order->status] ?? 'secondary';
                                    $labels = \App\Models\Order::getStatusOptions();
                                @endphp
                                <span
                                    class="badge badge-{{ $color }}">{{ $labels[$order->status] ?? $order->status }}</span>
                            </td>
                            <td>{{ $order->created_at->format('d.m.Y H:i') }}</td>
                            <td>
                                <a href="{{ route('admin.orders.show', $order->id) }}" class="btn btn-sm btn-info">
                                    <i class="fas fa-eye"></i>
                                </a>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
        <div class="card-footer">
            {{ $orders->links() }}
        </div>
    </div>
@stop
