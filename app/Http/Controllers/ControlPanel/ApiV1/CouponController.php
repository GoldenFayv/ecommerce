<?php

namespace App\Http\Controllers\ControlPanel\ApiV1;

use App\Models\Coupon;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

class CouponController extends Controller
{
    public function index()
    {
        $coupons = Coupon::all();
        return $this->successResponse("", $coupons);
    }

    public function show($id)
    {
        $coupon = Coupon::find($id);
        if (!$coupon) {
            return $this->failureResponse('Coupon not found');
        }
        return $this->successResponse("", $coupon);
    }

    public function store(Request $request)
    {
        $request->validate([
            'code' => 'required|unique:coupons',
            'discount_amount' => 'required',
            'type' => 'required|in:Percentage,Fixed',
            'target' => 'required|in:Product,Category,SubCategory,Order',
            'target_id' => 'required_unless:target,Order',
            'usage_limit' => 'required',
            'expires_at' => 'required|date',
            'active' => 'required|boolean'
        ]);

        $admin = auth("admin")->user();

        $couponData = $request->all();
        $couponData['admin_id'] = $admin->id;

        $coupon = Coupon::create($couponData);

        return $this->successResponse("Coupon Created", $coupon);
    }

    public function update(Request $request, $id)
    {
        $coupon = Coupon::find($id);
        if (!$coupon) {
            return $this->failureResponse('Coupon not found');
        }

        $request->validate([
            'code' => 'required|unique:coupons,code,' . $id,
        ]);

        $coupon->update($request->all());

        return $this->successResponse("", $coupon);
    }

    public function destroy($id)
    {
        $coupon = Coupon::find($id);
        if (!$coupon) {
            return $this->failureResponse('Coupon not found');
        }

        $coupon->delete();
        return $this->successResponse("Coupon deleted successfully");
    }
}

