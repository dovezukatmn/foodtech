@extends('adminlte::page')

@section('title', 'Канбан заказов')

@section('content_header')
    <div class="d-flex justify-content-between">
        <h1>Канбан-доска</h1>
        <a href="{{ route('admin.orders.index') }}" class="btn btn-default">
            <i class="fas fa-list"></i> Список
        </a>
    </div>
@stop

@section('content')
    <div class="container-fluid h-100">
        <div class="row flex-row flex-nowrap overflow-auto" style="min-height: 80vh;">
            @foreach ($columns as $status => $column)
                <div class="col-3">
                    <div class="card card-row card-default">
                        <div class="card-header bg-light">
                            <h3 class="card-title">
                                {{ $column['label'] }}
                                <span class="badge badge-secondary float-right">{{ $column['orders']->count() }}</span>
                            </h3>
                        </div>
                        <div class="card-body p-2 kanban-column" data-status="{{ $status }}">
                            @foreach ($column['orders'] as $order)
                                <div class="card card-light card-outline mb-2 kanban-item" data-id="{{ $order->id }}">
                                    <div class="card-header">
                                        <h5 class="card-title">Заказ #{{ $order->id }}</h5>
                                        <div class="card-tools">
                                            <a href="{{ route('admin.orders.show', $order->id) }}"
                                                class="btn btn-tool btn-link">
                                                <i class="fas fa-pen"></i>
                                            </a>
                                        </div>
                                    </div>
                                    <div class="card-body p-2">
                                        <p class="m-0">{{ $order->user->name ?? $order->phone }}</p>
                                        <p class="m-0 text-bold">{{ number_format($order->total_price, 0, '.', ' ') }} ₽</p>
                                        <small class="text-muted">{{ $order->created_at->format('H:i') }}</small>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    </div>
                </div>
            @endforeach
        </div>
    </div>
@stop

@section('css')
    <style>
        .kanban-column {
            min-height: 500px;
        }

        .gu-mirror {
            position: fixed !important;
            margin: 0 !important;
            z-index: 9999 !important;
            opacity: 0.8;
            -ms-filter: "progid:DXImageTransform.Microsoft.Alpha(Opacity=80)";
            filter: alpha(opacity=80);
        }

        .gu-hide {
            display: none !important;
        }

        .gu-unselectable {
            -webkit-user-select: none !important;
            -moz-user-select: none !important;
            -ms-user-select: none !important;
            user-select: none !important;
        }

        .gu-transit {
            opacity: 0.2;
            -ms-filter: "progid:DXImageTransform.Microsoft.Alpha(Opacity=20)";
            filter: alpha(opacity=20);
        }
    </style>
@stop

@section('js')
    <script src="https://cdnjs.cloudflare.com/ajax/libs/dragula/3.7.3/dragula.min.js"></script>
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            var containers = Array.from(document.querySelectorAll('.kanban-column'));

            dragula(containers).on('drop', function(el, target, source, sibling) {
                var orderId = el.getAttribute('data-id');
                var newStatus = target.getAttribute('data-status');

                // Отправляем AJAX запрос на обновление статуса
                fetch(`/admin/orders/${orderId}/update-status`, {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': '{{ csrf_token() }}'
                        },
                        body: JSON.stringify({
                            status: newStatus
                        })
                    })
                    .then(response => response.json())
                    .then(data => {
                        if (data.success) {
                            toastr.success('Статус обновлен');
                        } else {
                            toastr.error('Ошибка обновления');
                        }
                    })
                    .catch(error => {
                        toastr.error('Ошибка сети');
                        console.error('Error:', error);
                    });
            });
        });
    </script>
@stop
