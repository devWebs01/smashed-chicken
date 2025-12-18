<?php

namespace App\Http\Controllers\Api;

use App\Models\Order;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class OrderController extends BaseApiController
{
    /**
     * Display a listing of orders.
     */
    public function index(Request $request): JsonResponse
    {
        $orders = Order::query()
            ->with(['orderItems.product', 'device'])
            ->when($request->status, function ($query, $status) {
                $query->where('status', $status);
            })
            ->when($request->date_from, function ($query, $dateFrom) {
                $query->whereDate('order_date_time', '>=', $dateFrom);
            })
            ->when($request->date_to, function ($query, $dateTo) {
                $query->whereDate('order_date_time', '<=', $dateTo);
            })
            ->when($request->customer_name, function ($query, $customerName) {
                $query->where('customer_name', 'like', "%{$customerName}%");
            })
            ->when($request->sort_by, function ($query, $sortBy) {
                $direction = $request->get('sort_direction', 'desc');
                $query->orderBy($sortBy, $direction);
            }, function ($query) {
                $query->orderBy('order_date_time', 'desc');
            })
            ->paginate($request->get('per_page', 15));

        // Format the orders data
        $formattedOrders = $orders->getCollection()->map(function ($order) {
            return [
                'id' => $order->id,
                'customer_name' => $order->customer_name,
                'customer_phone' => $order->customer_phone,
                'customer_address' => $order->customer_address,
                'status' => $order->status,
                'order_date_time' => $order->order_date_time,
                'payment_method' => $order->payment_method,
                'delivery_method' => $order->delivery_method,
                'total_price' => $order->total_price,
                'device' => $order->device ? [
                    'id' => $order->device->id,
                    'name' => $order->device->name,
                ] : null,
                'items' => $order->orderItems->map(function ($item) {
                    return [
                        'id' => $item->id,
                        'product' => [
                            'id' => $item->product->id,
                            'name' => $item->product->name,
                            'price' => $item->product->price,
                        ],
                        'quantity' => $item->quantity,
                        'subtotal' => $item->subtotal,
                    ];
                }),
                'created_at' => $order->created_at,
                'updated_at' => $order->updated_at,
            ];
        });

        return response()->json([
            'success' => true,
            'message' => 'Orders retrieved successfully',
            'data' => [
                'orders' => $formattedOrders,
                'pagination' => [
                    'current_page' => $orders->currentPage(),
                    'per_page' => $orders->perPage(),
                    'total' => $orders->total(),
                    'last_page' => $orders->lastPage(),
                    'has_more' => $orders->hasMorePages(),
                ],
                'filters' => [
                    'available_statuses' => [
                        Order::STATUS_DRAFT,
                        Order::STATUS_PENDING,
                        Order::STATUS_CONFIRM,
                        Order::STATUS_PROCESSING,
                        Order::STATUS_COMPLETED,
                        Order::STATUS_CANCELLED,
                    ],
                ],
            ],
        ]);
    }

    /**
     * Display the specified order.
     */
    public function show(Order $order): JsonResponse
    {
        $order->load(['orderItems.product', 'device']);

        $formattedOrder = [
            'id' => $order->id,
            'customer_name' => $order->customer_name,
            'customer_phone' => $order->customer_phone,
            'customer_address' => $order->customer_address,
            'status' => $order->status,
            'order_date_time' => $order->order_date_time,
            'payment_method' => $order->payment_method,
            'delivery_method' => $order->delivery_method,
            'total_price' => $order->total_price,
            'device' => $order->device ? [
                'id' => $order->device->id,
                'name' => $order->device->name,
            ] : null,
            'items' => $order->orderItems->map(function ($item) {
                return [
                    'id' => $item->id,
                    'product' => [
                        'id' => $item->product->id,
                        'name' => $item->product->name,
                        'description' => $item->product->description,
                        'price' => $item->product->price,
                        'image' => $item->product->image,
                    ],
                    'quantity' => $item->quantity,
                    'subtotal' => $item->subtotal,
                ];
            }),
            'created_at' => $order->created_at,
            'updated_at' => $order->updated_at,
        ];

        return response()->json([
            'success' => true,
            'message' => 'Order retrieved successfully',
            'data' => [
                'order' => $formattedOrder,
            ],
        ]);
    }
}
