<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Order;
use Illuminate\Http\Request;

class OrderController extends Controller
{
    public function index()
    {
        $orders = Order::latest()->paginate(20);
        return view('admin.orders.index', compact('orders'));
    }

    public function show(Order $order)
    {
        return view('admin.orders.show', compact('order'));
    }

    public function kanban()
    {
        $statuses = Order::getStatusOptions();

        // Группируем заказы по статусам
        $columns = [];
        foreach ($statuses as $status => $label) {
            $columns[$status] = [
                'label' => $label,
                'orders' => Order::where('status', $status)->latest()->get()
            ];
        }

        return view('admin.orders.kanban', compact('columns'));
    }

    public function updateStatus(Request $request, Order $order)
    {
        $request->validate([
            'status' => 'required|string'
        ]);

        // Здесь можно добавить проверку canTransitionTo из модели
        $order->update(['status' => $request->status]);

        return response()->json(['success' => true]);
    }
}
