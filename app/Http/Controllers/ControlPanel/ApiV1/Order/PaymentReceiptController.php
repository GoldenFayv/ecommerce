<?php

namespace App\Http\Controllers\ControlPanel\ApiV1\sale;

use App\Http\Controllers\Controller;
use App\Models\user\UserPayments;
use App\Services\user\OrderService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class PaymentReceiptController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $result = [];
        $receiptsIds = UserPayments::get()->pluck("id");
        foreach ($receiptsIds as $receiptId) {
            $result[] = $this->getDetail($receiptId);
        }
        return $this->successResponse("", $result);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        $receipt = $this->getDetail($id);
        if ($receipt) {
            return $this->successResponse("", $receipt);
        } else {
            return $this->failureResponse("Receipt Not Found");
        }
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
    public function getDetail($id)
    {
        $receipt = UserPayments::Where("id", $id)->first();
        $orderService = new OrderService();

        if ($receipt) {
            return [
                "id" => $receipt->id,
                "image" => Storage::url("uploads/sales/receipt/" . $receipt->receipt),
                "order" => $orderService->get_order_detail($receipt->order_id),
            ];
        } else {
            return false;
        }
    }
}
