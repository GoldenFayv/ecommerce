<?php

namespace App\Services;

use Carbon\Carbon;
use App\Models\User\User;
use App\Models\Order\Order;
use App\Models\sale\Payment;
use App\Models\user\Transaction;
use App\Http\Controllers\General;
use App\Services\User\PaymentService;
use App\Services\User\NotificationService;

class TransactionService
{

    /**
     * @param bool $isUser This is Used to Control what is returned if it's an Admin or User
     */
    public function getTransactionDetail($id = null, array|Transaction $transaction = null, $isUser = false)
    {
        if (!$transaction) {
            $transaction = Transaction::where("id", $id)->with(["admin"])->first();
        }

        $result = [
            "amount" => number_format($transaction["amount"], 2),
            "method" => $transaction["payment_method"],
            "type" => $transaction["type"],
            "reference" => $transaction["reference"],
            "purpose" => $transaction["purpose"],
            "order_id" => $transaction["order_id"],
            "date" => $transaction["created_at"],

        ];
        if (!$isUser) {
            $admin = $transaction["admin"];
            $result["admin"] = $admin ? [
                "id" => $admin["id"],
                "name" => $admin["last_name"] . ', ' . $admin["first_name"]
            ] : [
                "id" => null,
                "name" => General::BOT
            ];
            $result["date"] = (new Carbon($transaction["created_at"]))->format("h:i:sA dS M Y");
        }
        return $result;
    }
    public function creditUser($amount, $note, $method, $user_id, $admin_id)
    {
        $reference = generateRef(model: Transaction::class);
        $user =  User::where("id", $user_id)->first();
        
        $user->transactions()->create([
            "note" => $note,
            "amount" => $amount,
            "admin_id" => $admin_id,
            "payment_method" => $method,
            "reference" => $reference
        ]);

        $notificationService = new NotificationService();
        $notificationService->create("Account Credited", "Your Account Has Been Credited with ₦$amount", $user_id);

        $balance = $this->clearUserDue($notificationService, $user_id, $amount, $reference);
        if ($balance > 0) {
            $user->increment("balance", $balance);
        }
    }

    /**
     * Clears all the user orders and returns the remaining amount
     */
    public function clearUserDue(NotificationService $notificationService, $user_id, $amount, $reference, $fromUserBalance =  false)
    {

        $paymentService = new PaymentService();
        $orders = Order::where("user_id", $user_id)->where("payment_status", "!=", "Paid")->orderBy("created_at")->get();
        foreach ($orders as $order) {
            $due = $order->due;
            if ($amount > 0) {

                /** Pay Only the Due or The Left Amount If the Amount is less then the due */
                if ($amount > $due) {
                    $paying_amount = $due;
                } else {
                    $paying_amount = $amount;
                }

                $note = $fromUserBalance ? "Payment Made Automatically from User's Balance" : "Payment Made Automatically with [$reference] Transaction";
                $notification_message = $fromUserBalance ? "A Payment was made for Order [{$order->reference}] from your Balance" :  "A Payment was made for Order [{$order->reference}] with [$reference] Transaction";
                $paymentService->make_order_payment($order->id, $paying_amount, reference: $reference, method: "Balance", note: $note);
                $notificationService->create("Order Payment", $notification_message, $user_id);
                $amount -= $paying_amount;
            } else {

                break;
            }
        }
        return max($amount, 0);
    }
}
