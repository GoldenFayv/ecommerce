<?php

namespace App\Services\User;

use Carbon\Carbon;
use App\Models\Order\Order;

class OrderService
{
    public function getOrderDetail(int $id = null, Order $order = null)
    {
        if ($id) {
            $order = Order::where("id", $id)->first();
        }
        if ($order) {

            $paymentService = new PaymentService();

            $paid = $order->total_payment;
            return [
                "id" => $order->id,
                "status" => $order->status,
                "payment_status" => $order->payment_status,
                "reference" => $order->reference,
                "payment_method" => $order->payment_method,
                "delivery_method" => $order->delivery_method,
                "delivery_cost" => number_format($order->delivery_cost, 2),
                "total" => number_format($order->total, 2),
                "date" => (new Carbon($order->created_at))->format("h:i:sA dS M Y"),
                "sub_total" => number_format($order->sub_total, 2),
                "discount" => number_format($order->discount, 2),
                "shipping" => number_format($order->shipping, 2),
                "due" => number_format($order->due, 2),
                "paid" => number_format($paid, 2),
                "products" => $order->orderProducts ? $order->orderProducts->map(function ($orderProduct) {
                    $product = $orderProduct->product;
                    return [
                        "id" => $product->id,
                        "name" => $product->name,
                        "price" => $orderProduct->product_price,
                        "qty" => $orderProduct->qty,
                        "discount" => $orderProduct->discount,
                        "sub_total" => $orderProduct->sub_total,
                        "image" => $product->firstImage

                    ];
                }) : null,
                "payments" => $order->payments->map(fn($payment) =>$paymentService->getDetail(payment: $payment))
            ];
        }
    }
}
