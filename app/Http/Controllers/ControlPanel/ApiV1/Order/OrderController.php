<?php

namespace App\Http\Controllers\ControlPanel\ApiV1\Order;

use App\Models\User\User;
use App\Models\Admin\Admin;
use App\Models\Order\Order;
use Illuminate\Http\Request;
use App\Services\UserService;
use App\Models\Product\Product;
use App\Http\Controllers\Controller;
use App\Services\Admin\OrderService;
use Illuminate\Support\Facades\Auth;
use App\Services\User\NotificationService;
use App\Services\OrderService as ServicesOrderService;

class OrderController extends Controller
{
    public function index(Request $request, OrderService $orderService)
    {
        $admin = Auth::guard("admin")->user();

        $ids = Order::get()->pluck('id')->toArray();

        $result = array_map(function ($id) use ($orderService) {
            return $orderService->getOrderDetail($id);
        }, $ids);

        return $this->successResponse("", $result);
    }

    public function show(OrderService $orderService, $id)
    {
        return $this->successResponse("", $orderService->getOrderDetail($id));
    }

    public function store(Request $request, OrderService $orderService)
    {
        $request->validate([
            "user_account_number" => "required|exists:users,account_number",
            "products" => "required|array",
            "products.*.id" => "required|exists:products,id",
            "products.*.qty" => "required|integer|min:1",
        ]);

        try {
            $userAccountNumber = $request["user_account_number"];
            $user = User::where("account_number", $userAccountNumber)->first();
            $orderProducts = $request['products'];

            $check = Product::whereIn("id", array_column($orderProducts, "id"))->where("quantity", "=", 0)->first();
            if ($check) {
                return $this->failureResponse($check->name . " is Out Of Stock");
            }

            $admin = Auth::guard("admin")->user();

            // check for errors
            $error = null;
            $errorCheck = ServicesOrderService::orderIsValid($orderProducts, $user->id);

            if ($errorCheck) {
                return $this->failureResponse($errorCheck);
            } else {
                $order = ServicesOrderService::placeOrder($user->id, $orderProducts, adminId: $admin->id);
                return $this->successResponse("Order Placed", $orderService->getOrderDetail(order: $order));
            }

        } catch (\Throwable $th) {
            return $this->failureResponse("Unable to Place Order", th: $th);
        }
    }

    public function summary(Request $request, OrderService $orderService)
    {
        $request->validate([
            "user_account_number" => "required|exists:users,account_number",
            "products" => "required|array",
            "products.*.id" => "required|exists:products,id",
            "products.*.qty" => "required|integer|min:1",
        ]);

        try {
            $userAccountNumber = $request["user_account_number"];
            $user = User::where("account_number", $userAccountNumber)->first();
            $orderProducts = $request['products'];

            $check = Product::whereIn("id", array_column($orderProducts, "id"))->where("quantity", "=", 0)->first();
            if ($check) {
                return $this->failureResponse($check->name . " is Out Of Stock");
            }

            // check for errors
            $error = null;
            $errorCheck = ServicesOrderService::orderIsValid($orderProducts, $user->id);
            if ($errorCheck) {
                return $this->failureResponse($errorCheck);
            } else {
                $summary = ServicesOrderService::getOrderSummary($orderProducts, userId: $user->id);

                return $this->successResponse("Order Summary", $summary, status: 201);
            }
        } catch (\Throwable $th) {
            return $this->failureResponse("Unable to Fetch Order Summary", th: $th);
        }
    }

    public function destroy(Request $request, NotificationService $notificationService, UserService $userService, ServicesOrderService $orderService, $id)
    {
        $request->validate([
            "charge_user" => "in:0,1"
        ]);
        $chargeUser = $request["charge_user"] == 1 ? true : false;
        $deleteOrder = $orderService->deleteOrder($notificationService, $userService, $id, $chargeUser);

        if ($deleteOrder) {
            return $this->successResponse("Order Deleted");
        } else {
            return $this->failureResponse("Order not Found");
        }
    }
}
