<?php

namespace App\Http\Controllers\ControlPanel\ApiV1\User;

use App\Models\sale\Order;
use App\Models\User;
use App\Services\cpanel\OrderService;
use Illuminate\Http\Request;
use App\Models\user\Transaction;
use App\Http\Controllers\Controller;
use App\Services\cpanel\UserService;
use App\Services\NotificationService;
use App\Services\user\TransactionService;

class TransactionsController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(UserService $userService, TransactionService $transactionService, OrderService $orderService, $acct_no_Or_id)
    {
        $user_id = User::where("id", $acct_no_Or_id)
            ->orWhere("account_number", $acct_no_Or_id)->first()->id;
        $transactions = Transaction::where("user_id", $user_id)->with(["admin"])->orderBy("created_at", "DESC")->get();

        $user_transactions = $transactions->map(fn($transaction) => $transactionService->getTransactionDetail(transaction: $transaction));

        $dues = Order::where("user_id", $user_id)->where("payment_status", "!=", "Paid")->where("status", '!=', "Returned")->get();
        $outstanding = $dues->map(fn($order) => $orderService->get_order_detail($order->id));
        $result = [
            "user" => $userService->getUserAccountDetails($user_id),
            "transactions" => $user_transactions,
            "outstanding" => $outstanding
        ];
        return $this->successResponse("", $result);
    }


    /**
     * Store a newly created resource in storage.
     */
    public function creditUser(Request $request, NotificationService $notificationService, TransactionService $transactionService, $accountNo)
    {
        $request->validate([
            "amount" => "required|numeric|min:0",
            "method" => "required|in:Cash,POS,Bank / Transfer,Card",
            "note" => "required|string",
        ]);
        try {
            $user_id = User::where("account_number", $accountNo)->first()->id;
            $admin_id = auth()->guard("admin")->user()->id;
            $transactionService->creditUser($notificationService, $request["amount"], $request["note"], $request["method"], $user_id, $admin_id);
            return $this->successResponse("User Account Credited");
        } catch (\Throwable $th) {
            return $this->failureResponse("Unable to Process Request", th: $th);
        }
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        //
    }
}
