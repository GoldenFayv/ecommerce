<?php

namespace App\Services\User;

use App\Models\User\User;
use App\Models\Order\Order;
use App\Models\Order\Payment;
use App\Models\user\Transaction;
use App\Services\User\NotificationService;


class PaymentService
{
    /**
     * Retrieves details of a payment by its ID.
     *
     * @param int $paymentId The ID of the payment.
     *
     * @return array|null Payment details if found, otherwise null.
     */
    public function getDetail($paymentId = null, Payment $payment = null)
    {
        if ($paymentId) {
            $payment = Payment::where("id", $paymentId)->first();
        }

        if ($payment) {
            return [
                "amount" => number_format($payment->amount, 2),
                "reference" => $payment->reference,
                "method" => $payment->method,
                "date" => $payment->created_at->format("h:i:sa dS-M-Y"),
                "paid_by" => $payment->paid_by
            ];
        }

        return null;
    }


    /**
     * Makes a payment for an order from a user's balance.
     *
     * @param int $orderId The ID of the order.
     *
     * @return bool True if the payment was completed; otherwise, false.
     */
    public function makeOrderPaymentFromBalance($orderId)
    {
        $order = Order::where("id", $orderId)->first();
        $orderDue = $order->due;

        $userId = $order->user_id;
        $user = User::where("id", $userId)->first();
        $userBalance = $user->balance;

        if ($userBalance >= $orderDue) {
            $paymentRef = generateRef(model: Transaction::class);

            $notificationService = new NotificationService();
            $note = "Payment Made Automatically from User's Balance";
            $notificationMessage = "A Payment was made for Order [{$order->reference}] from your Balance";
            $this->makeOrderPayment($orderId, $orderDue, $paymentRef, "Balance", $note);
            $notificationService->create("Order Payment", $notificationMessage, $userId, "Order", $orderId);

            $user->decrement("balance", $orderDue);
            return true;
        }
        return false;
    }

    /**
     * Makes a payment for an order.
     *
     * @param int $orderId The ID of the order.
     * @param float $amount The amount to be paid.
     * @param string $reference The payment reference.
     * @param string $method The payment method.
     * @param string $note Additional note for the payment.
     * @param int|null $adminId Admin ID (optional).
     *
     * @return void
     */
    public function makeOrderPayment($orderId, $amount, $reference, $method, $note, $adminId = null)
    {
        $order = Order::where("id", $orderId)->first();
        $userId = $order->user_id;
        // get the old due
        $previousDue = $order->due;

        $payment = $order->payments()->create([
            "reference" => $reference,
            "amount" => $amount,
            "user_id" => $userId,
            "method" => $method,
            "note" => $note,
            "admin_id" => $adminId ?? null,
        ]);

        $payment->transaction()->create([
            "amount" => $amount,
            "type" => "Debit",
            "description" => $note,
            "user_id" => $userId,
            "reference" => $reference,
        ]);

        $order = Order::where("id", $orderId)->first();

        // check if the payment has been completed
        $totalPaidAmount = $order->payments->sum("amount");

        if ($totalPaidAmount >= $order->total) {
            $order->payment_status = "Paid";
        } else {
            $order->payment_status = "Incomplete";
        }
        $order->save();
    }

}
