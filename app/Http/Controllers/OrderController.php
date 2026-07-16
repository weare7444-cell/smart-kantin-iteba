<?php

namespace App\Http\Controllers;

use App\Models\Order;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class OrderController extends Controller
{
    public function store(Request $request)
    {
        $data = $request->validate([
            'stall_id'    => 'required|integer',
            'total'       => 'required|numeric|min:0',
            'pickup_time' => 'required|string',
            'items'       => 'required|array',
            'items.*.name'  => 'required|string',
            'items.*.qty'   => 'required|integer|min:1',
            'items.*.price' => 'required|numeric|min:0',
        ]);

        $order = Order::create([
            'user_id'     => Auth::id(),
            'stall_id'    => $data['stall_id'],
            'total'       => $data['total'],
            'pickup_time' => $data['pickup_time'],
            'status'      => 'pending',
            'items'       => $data['items'],
        ]);

        return response()->json($order, 201);
    }

    public function byStall($stallId)
    {
        $orders = Order::where('stall_id', $stallId)
            ->orderBy('created_at', 'desc')
            ->get();

        return response()->json($orders);
    }

    public function updateStatus(Request $request, $id)
    {
        $data = $request->validate([
            'status' => 'required|in:pending,processing,ready,completed,canceled',
        ]);

        $order = Order::findOrFail($id);
        $order->update(['status' => $data['status']]);

        return response()->json($order);
    }
}
