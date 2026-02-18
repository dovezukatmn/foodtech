@extends('adminlte::page')

@section('title', 'Заказ #' . $order->id)

@section('content_header')
    <div class="d-flex justify-content-between">
        <h1>Заказ #{{ $order->id }}</h1>
        <a href="{{ route('admin.orders.index') }}" class="btn btn-default">Назад к списку</a>
    </div>
@stop

@section('content')
    <div class="row">
        <div class="col-md-4">
            <div class="card card-primary card-outline">
                <div class="card-body box-profile">
                    <h3 class="profile-username text-center">{{ $order->user->name ?? 'Гость' }}</h3>
                    <p class="text-muted text-center">{{ $order->phone }}</p>
                    <ul class="list-group list-group-unbordered mb-3">
                        <li class="list-group-item">
                            <b>Сумма</b> <a class="float-right">{{ number_format($order->total_price, 0, '.', ' ') }} ₽</a>
                        </li>
                        <li class="list-group-item">
                            <b>Дата</b> <a class="float-right">{{ $order->created_at->format('d.m.Y H:i') }}</a>
                        </li>
                        <li class="list-group-item">
                            <b>Статус</b>
                            <span
                                class="float-right badge badge-{{ \App\Models\Order::getStatusColors()[$order->status] ?? 'secondary' }}">
                                {{ \App\Models\Order::getStatusOptions()[$order->status] ?? $order->status }}
                            </span>
                        </li>
                    </ul>

                    <form action="{{ route('admin.orders.update-status', $order->id) }}" method="POST">
                        @csrf
                        <div class="form-group">
                            <label>Изменить статус</label>
                            <select name="status" class="form-control" onchange="this.form.submit()">
                                @foreach (\App\Models\Order::getStatusOptions() as $key => $label)
                                    <option value="{{ $key }}" {{ $order->status == $key ? 'selected' : '' }}>
                                        {{ $label }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        <div class="col-md-8">
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">Состав заказа</h3>
                </div>
                <div class="card-body p-0">
                    <table class="table table-striped">
                        <thead>
                            <tr>
                                <th>Товар</th>
                                <th>Цена</th>
                                <th>Кол-во</th>
                                <th>Сумма</th>
                            </tr>
                        </thead>
                        <tbody>
                            {{-- Здесь должен быть цикл по позициям заказа --}}
                            {{-- @foreach ($order->items as $item) --}}
                            <tr>
                                <td colspan="4" class="text-center text-muted">Информация о позициях будет доступна после
                                    настройки связи order_items</td>
                            </tr>
                            {{-- @endforeach --}}
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
@stop
