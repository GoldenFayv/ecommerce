<?php

namespace App\Services;

use App\Models\Coupon;
use App\Models\Product\Product;


class CouponService
{
    public function processCoupon($couponCode, $products)
    {
        $coupon = Coupon::where('code', $couponCode)->first();

        if (!$coupon) {
            return ['message' => 'Coupon not found'];
        }

        foreach ($products as $product) {
            $couponCheck = $this->isProductEligibleForCoupon($couponCode, $product->id);
        }

        return ['message' => 'Coupon processed successfully'];
    }

    /**
     * Check if an order is eligible for a coupon and determine the coupon discount.
     *
     * @param string $couponCode The code of the coupon to check.
     * @param float $orderTotal The total value of the order to apply the coupon to.
     *
     * @return mixed Returns the coupon discount if the order is eligible; otherwise, it returns false.
     *               If the coupon is applicable, it returns the coupon discount amount based on the order total.
     *               If the coupon doesn't apply or is not targeted for the order, it returns false.
     */
    public function isOrderEligbleForCoupon($couponCode, $orderTotal)
    {
        $coupon = Coupon::where('code', $couponCode)->first();

        if (!$coupon || $coupon->target !== 'Order') {
            return false; // Coupon not found or not targeted for the order
        }

        if ($coupon->type == "Fixed") {
            $couponDiscount = $coupon->discount_amount;
        } else {
            $couponDiscount = ($coupon->discount_amount / 100) * $orderTotal;
        }

        return $couponDiscount;
    }
    
    /**
     * Check if a product is eligible for a coupon and determine the coupon discount.
     *
     * @param string $couponCode The code of the coupon to check.
     * @param int $productId The ID of the product to check against the coupon.
     *
     * @return object Returns an object with 'status' and 'couponDiscount' properties.
     *                - 'status' indicates the eligibility status: true for eligible, false for various reasons.
     *                - 'couponDiscount' contains the coupon discount if eligible; otherwise, it's false.
     *                If errors occur, the function returns an object with 'status' set to false and 'message' providing error details:
     *                - 'message' describes potential issues such as 'Coupon or Product not found', 'Coupon has finished', 'Coupon has expired', etc.
     */
    function isProductEligibleForCoupon($couponCode, $productId)
    {
        $coupon = Coupon::where('code', $couponCode)->first();
        $product = Product::find($productId);

        if (!$coupon || !$product) {
            return (object) ['status' => false, 'message' => "Coupon or Product not Found"]; // Coupon or Product not found
        }

        if ($coupon->quantity == 0) {
            return (object) ['status' => false, 'message' => "Coupon Has Finished"];
        }
        if ($coupon->expires_at < now()) {
            return (object) ['status' => false, 'message' => "Coupon Has Expired"];
        }

        $targetId = $coupon->target_id;
        $target = $coupon->target;
        $type = $coupon->type;

        if ($type == "Fixed") {
            $couponDiscount = $coupon->discount_amount;
        } else {
            $couponDiscount = ($coupon->discount_amount / 100) * $product->selling_price;
        }

        if ($target === 'Order') {
            return (object) ['status' => false, 'message' => "Coupon applies to the entire order"]; // Coupon applies to the entire order
        }

        $matchesTarget =
            ($target === 'Category' && $product->sub_category->category_id == $targetId) ||
            ($target === 'SubCategory' && $product->subcategory_id == $targetId) ||
            ($target === 'Product' && $productId == $targetId);

        return (object) ['status' => true, 'couponDiscount' => $matchesTarget ? $couponDiscount : false];
    }
}

