<?php

namespace App\Services\Admin;

use App\Models\admin\Admin;
use App\Models\Order\Order;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Auth;

class OrderService
{

    public function getOrderDetail(int $id = null, Order $order = null)
    {
        if ($id) {
            $order = Order::where("id", $id)->first();
        }

        /**
         * @var Admin
         */
        $admin = Auth::guard("admin")->user();

        if ($order) {
            $paid = $order->total_payment;

            if ($admin->isSuperAdmin()) {
                $email = $order->user->email;
                $mobile_number = $order->user->mobile_number;
            } else {

                // Replace the part before @ in the email and mobile number
                $raw_email = $order->user->email;
                $email = preg_replace("/(.*)@/", "********@", $raw_email);

                $raw_mobile_number = $order->user->mobile_number;
                $mobile_number_length = strlen($raw_mobile_number);
                $mobile_number = str_repeat("*", 10) . substr($raw_mobile_number, $mobile_number_length - 2, $mobile_number_length);
            }

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
                "user" => [
                    "id" => $order->user->id,
                    "name" => $order->user->name,
                    "email" => $email,
                    "mobile_number" => $mobile_number,
                ],
                "products" => $order->orderProducts ? $order->orderProducts->map(function ($order_product) {
                    $product = $order_product->product;
                    return [
                        "id" => $product->id,
                        "name" => $product->name,
                        "price" => $order_product->product_price,
                        "qty" => $order_product->qty,
                        "discount" => $order_product->discount,
                        "sub_total" => $order_product->sub_total,
                        "image" => $product->firstImage

                    ];
                }) : null,
                "payments" => $admin->canAccess(null, "access-payments") ? $order->payments->map(fn($payment) =>
                    [
                        "amount" => number_format($payment->paid_amount, 2),
                        "reference" => $payment->reference,
                        "method" => $payment->method,
                        "date" => $payment->created_at->format("h:i:sa dS-M-Y"),
                        "paid_by" => $payment->paid_by
                    ]) : null,
            ];
        }
    }
}
