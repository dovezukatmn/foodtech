<x-filament-panels::page>
    <style>
        .kanban-board {
            display: grid;
            grid-template-columns: repeat(6, 1fr);
            gap: 1rem;
            overflow-x: auto;
            padding-bottom: 1rem;
        }

        @media (max-width: 1400px) {
            .kanban-board {
                grid-template-columns: repeat(3, 1fr);
            }
        }

        @media (max-width: 768px) {
            .kanban-board {
                grid-template-columns: 1fr;
            }
        }

        .kanban-column {
            background: rgba(var(--gray-100), 0.5);
            border-radius: 0.75rem;
            padding: 0.75rem;
            min-height: 300px;
        }

        .dark .kanban-column {
            background: rgba(255, 255, 255, 0.05);
        }

        .kanban-column-header {
            display: flex;
            align-items: center;
            gap: 0.5rem;
            padding: 0.5rem;
            margin-bottom: 0.75rem;
            border-radius: 0.5rem;
            font-weight: 600;
            font-size: 0.85rem;
        }

        .kanban-column-count {
            background: rgba(0, 0, 0, 0.15);
            border-radius: 9999px;
            padding: 0.15rem 0.5rem;
            font-size: 0.75rem;
            margin-left: auto;
        }

        .kanban-card {
            background: white;
            border-radius: 0.5rem;
            padding: 0.75rem;
            margin-bottom: 0.5rem;
            border: 1px solid rgba(var(--gray-200), 1);
            box-shadow: 0 1px 2px rgba(0, 0, 0, 0.05);
            transition: all 0.2s;
            cursor: pointer;
        }

        .dark .kanban-card {
            background: rgba(255, 255, 255, 0.05);
            border-color: rgba(255, 255, 255, 0.1);
        }

        .kanban-card:hover {
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1);
            transform: translateY(-1px);
        }

        .kanban-card-header {
            display: flex;
            align-items: center;
            justify-content: space-between;
            margin-bottom: 0.4rem;
        }

        .kanban-card-number {
            font-weight: 700;
            font-size: 0.85rem;
            color: rgb(var(--primary-600));
        }

        .kanban-card-amount {
            font-weight: 600;
            font-size: 0.8rem;
            color: rgb(var(--success-600));
        }

        .kanban-card-customer {
            font-size: 0.8rem;
            color: rgb(var(--gray-600));
            margin-bottom: 0.25rem;
        }

        .dark .kanban-card-customer {
            color: rgb(var(--gray-400));
        }

        .kanban-card-time {
            font-size: 0.7rem;
            color: rgb(var(--gray-400));
        }

        .kanban-card-actions {
            display: flex;
            gap: 0.25rem;
            margin-top: 0.5rem;
            flex-wrap: wrap;
        }

        .kanban-btn {
            font-size: 0.7rem;
            padding: 0.2rem 0.5rem;
            border-radius: 0.375rem;
            border: none;
            cursor: pointer;
            font-weight: 500;
            transition: all 0.15s;
            color: white;
        }

        .kanban-btn:hover {
            opacity: 0.85;
            transform: scale(1.02);
        }

        .kanban-btn-next {
            background: rgb(var(--primary-600));
        }

        .kanban-btn-cancel {
            background: rgb(var(--danger-600));
        }

        .kanban-empty {
            text-align: center;
            padding: 2rem 0.5rem;
            color: rgb(var(--gray-400));
            font-size: 0.8rem;
        }
    </style>

    <div class="kanban-board" wire:poll.10s>
        @php
            $statuses = $this->getKanbanStatuses();
            $ordersByStatus = $this->getOrdersByStatus();
            $transitions = \App\Models\Order::getAllowedTransitions();
            $statusOptions = \App\Models\Order::getStatusOptions();
        @endphp

        @foreach ($statuses as $statusKey => $statusInfo)
            <div class="kanban-column">
                <div class="kanban-column-header"
                    style="background: {{ $statusInfo['color'] }}20; color: {{ $statusInfo['color'] }};">
                    <span>{{ $statusInfo['icon'] }}</span>
                    <span>{{ $statusInfo['label'] }}</span>
                    <span class="kanban-column-count">{{ count($ordersByStatus[$statusKey] ?? []) }}</span>
                </div>

                @forelse($ordersByStatus[$statusKey] ?? [] as $order)
                    <div class="kanban-card">
                        <div class="kanban-card-header">
                            <a href="{{ route('filament.admin.resources.orders.view', $order) }}"
                                class="kanban-card-number">
                                #{{ $order->iiko_order_number ?? mb_substr($order->id, 0, 8) }}
                            </a>
                            <span class="kanban-card-amount">{{ number_format($order->total_amount, 0, ',', ' ') }}
                                ₽</span>
                        </div>
                        <div class="kanban-card-customer">
                            👤 {{ $order->customer_name ?? 'Без имени' }}
                        </div>
                        <div class="kanban-card-customer">
                            📞 {{ $order->customer_phone ?? '—' }}
                        </div>
                        @if ($order->delivery_address)
                            <div class="kanban-card-customer" title="{{ $order->delivery_address }}">
                                📍 {{ \Illuminate\Support\Str::limit($order->delivery_address, 30) }}
                            </div>
                        @endif
                        <div class="kanban-card-time">
                            🕐 {{ $order->created_at?->format('d.m H:i') ?? '—' }}
                        </div>

                        @php
                            $nextStatuses = $transitions[$statusKey] ?? [];
                        @endphp

                        @if (count($nextStatuses) > 0)
                            <div class="kanban-card-actions">
                                @foreach ($nextStatuses as $nextStatus)
                                    @if ($nextStatus === 'CANCELLED')
                                        <button class="kanban-btn kanban-btn-cancel"
                                            wire:click="moveOrder('{{ $order->id }}', '{{ $nextStatus }}')"
                                            wire:confirm="Отменить заказ #{{ $order->iiko_order_number ?? mb_substr($order->id, 0, 8) }}?">
                                            ✕ Отменить
                                        </button>
                                    @else
                                        <button class="kanban-btn kanban-btn-next"
                                            wire:click="moveOrder('{{ $order->id }}', '{{ $nextStatus }}')">
                                            → {{ $statusOptions[$nextStatus] ?? $nextStatus }}
                                        </button>
                                    @endif
                                @endforeach
                            </div>
                        @endif
                    </div>
                @empty
                    <div class="kanban-empty">
                        Нет заказов
                    </div>
                @endforelse
            </div>
        @endforeach
    </div>
</x-filament-panels::page>
