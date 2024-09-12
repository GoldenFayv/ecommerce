<?php

namespace App\Http\Controllers\ControlPanel\ApiV1\sale;

use App\Models\User;

use Illuminate\Http\Request;

use App\Http\Controllers\Controller;
use App\Services\NotificationService;

use App\Services\User\PaymentService;

use App\Services\user\TransactionService;

class PaymentController extends Controller
{
    public function index(PaymentService $paymentService, $order_id)
    {
        $result = $paymentService->get_order_payments($order_id);

        return $this->successResponse("", $result);
    }
    public function show(PaymentService $paymentService, $id)
    {
        return $this->successResponse("", $paymentService->get_detail($id));
    }
    public function store(Request $request, PaymentService $paymentService, $order_id)
    {
        $request->validate([
            "method" => "required|in:Cash,POS,Bank / Transfer,Cheque",
            "amount" => "required|min:0",
            "note" => "required|string",
        ]);
        try {
            $order_id = $request["order_id"];
            $admin_id = auth()->guard("admin")->user()->id;
            $reference = generateRef(model: \App\Models\sale\Payment::class);

            $paymentService->make_order_payment($order_id, $request["amount"], $reference, $request["method"], $request["note"],  $admin_id);
            return $this->successResponse("Payment Added", status: 201);
        } catch (\Throwable $th) {
            return $this->failureResponse("Unable To Add Payment", th: $th);
        }
    }
    public function clearUserDues(TransactionService $transactionService, NotificationService $notificationService, $accountNumber)
    {
        $user = User::where("account_number", $accountNumber)->first();
        $payment_ref = generateRef(model: \App\Models\user\Transaction::class);
        $balance = $user->balance;
        $user->balance = $transactionService->clearUserDue($notificationService, $user->id, $balance, $payment_ref, true);
        $user->save();
        return $this->successResponse("");
    }
}
