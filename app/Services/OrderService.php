<?php

namespace App\Services;

use App\Models\Crate;
use App\Models\Coupon;
use App\Models\User\User;
use App\Models\Admin\Admin;
use App\Models\Order\Order;
use App\Services\UserService;
use App\Models\Product\Product;
use App\Services\ProductServices;
use Illuminate\Support\Facades\Auth;
use App\Services\User\PaymentService;
use App\Services\User\NotificationService;

class OrderService
{
    /**
     * Creates an order product entry associated with the given order.
     *
     * @param Order $order The Order instance to which the product will be associated.
     * @param mixed $productId The ID of the product.
     * @param float $price The price of the product.
     * @param int $orderQty The quantity of the product within the order.
     * @param float $total The total price of the product within the order.
     * @param float $totalDiscount The total discount applied to the product within the order.
     * @param float $subTotal The subtotal of the product within the order.
     *
     * @return void
     */
    public static function createOrderProducts(Order $order, $productId, $price, $orderQty, $total, $totalDiscount, $subTotal)
    {
        $order->orderProducts()->create([
            "product_id" => $productId,
            "total" => $total,
            "sub_total" => $subTotal,
            "discount" => $totalDiscount,
            "product_price" => $price,
            "qty" => $orderQty
        ]);
    }

    /**
     * Places an order, calculates summary details, and handles payment methods.
     *
     * @param int $userId User ID of the order placer.
     * @param array $orderProducts Array containing product IDs and quantities:
     *   Sample: ```[ ["id" => 1, "qty" => 10], ["id" => 1, "qty" => 10]]```
     * @param string|null $deliveryMethod Delivery method for the order (optional).
     * @param string|null $paymentMethod Payment method for the order (optional).
     * @param int|null $adminId Admin ID (optional).
     *
     * @return Order The created Order instance.
     */
    public static function placeOrder(int $userId, array $orderProducts, string $deliveryMethod = null, string $paymentMethod = null, $adminId = null, $couponCode = null): Order
    {
        $orderRef = generateRef(model: Order::class);
        $deliveryMethod = $deliveryMethod ?? "Pick-Up";
        $paymentMethod = $paymentMethod ?? "Balance";
        $order = Order::create([
            "payment_method" => $paymentMethod,
            "delivery_method" => $deliveryMethod,
            "user_id" => $userId,
            "admin_id" => $adminId,
            "reference" => $orderRef
        ]);
        $orderId = $order->id;
        $orderTotal = $orderSubTotal = $orderTax = $orderDiscount = $orderDeliveryCost = 0;

        $orderSummary = self::getOrderSummary($orderProducts, function ($productId, $price, $orderQty, $total, $totalDiscount, $subTotal) use ($order) {
            self::createOrderProducts($order, $productId, $price, $orderQty, $total, $totalDiscount, $subTotal);
        }, $userId, $adminId, true, couponCode: $couponCode);

        // Assign the Order Related Variables
        $error = $orderSummary->error;
        $orderTotal = $orderSummary->orderTotal;
        $orderSubTotal = $orderSummary->orderSubTotal;
        $orderDiscount = $orderSummary->orderDiscount;

        $order->total = $orderTotal;
        $order->sub_total = $orderSubTotal;
        $order->discount = $orderDiscount;
        $order->save();

        // move order to sales
        if ($paymentMethod == "Balance") {
            $paymentService = new PaymentService();
            $orderIsPaid = $paymentService->makeOrderPaymentFromBalance($order->id);

            if ($orderIsPaid) {
                $order->status = "Processing";
                $order->save();

            }
        }

        return Order::find($order->id);
    }

    /**
     * Generates an Order Summary and Executes a Function if passed to the Closure while looping through order_products.
     * Used to create OrderProducts.
     *
     * @param array $orderProducts An Array Containing the Order Products.
     * @param \Closure|null $closure The Function to Execute while Looping on the Order Products.
     * @param int|null $userId User ID for the order.
     * @param bool $process If true, the Product Quantity Will Be Reduced.
     * @param string $couponCode The Coupon Code
     *
     * @return object Order Summary.
     * 
     * #### NOTE for Using the Closure:
     * The Closure should be constructed this way to access the datas:
     * ```php
     * <?php
     * function ($product->id, $price, $orderQty, $total, $totalDiscount, $subTotal) {
     *      // All these Variables are accessible within the Closure
     * }
     * getOrderSummary($orderProducts, function ($product->id, $price, $orderQty, $total, $totalDiscount, $subTotal) {
     *      // All these Variables are accessible within the Closure
     * });
     * ```
     */
    public static function getOrderSummary(array $orderProducts, ?\Closure $closure = null, ?int $userId = null, ?int $adminId = null, bool $process = false, $couponCode = null): object
    {
        // Initialize variables to store order summary details
        $error = false;
        $orderTotal = 0;
        $orderSubTotal = 0;
        $orderDiscount = 0;
        $totalQty = 0;
        $products = [];

        // Get user details
        $user = User::find($userId);
        $admin = Admin::find($adminId);

        /**
         * Check if a coupon is targeted for an order.
         *
         * @param string $couponCode The code of the coupon to check.
         *
         */
        $couponIsForOrder = false;
        if ($couponCode) {
            $coupon = Coupon::where("code", $couponCode)->first();
            if ($coupon->target == "Order") {
                $couponIsForOrder = true;
            }
        }

        $productService = new ProductServices();
        $couponService = new CouponService();
        // Loop through each order product
        foreach ($orderProducts as $orderProduct) {
            // Find the product details
            $product = Product::find($orderProduct['id']);

            // If product exists
            if ($product) {
                $orderQty = $orderProduct['qty'];
                $availableQty = $product->quantity;
                $discount = 0;

                // Check if ordered quantity exceeds available quantity
                if ($orderQty > $availableQty) {
                    $error = "{$product->name} Ordered Quantity Exceeds Available Quantity";
                    break;
                }


                // Calculate price and discount
                $price = $product->selling_price;
                if (!$couponIsForOrder && $couponCode) {
                    $couponResult = $couponService->isProductEligibleForCoupon($couponCode, $product->id);
                    if ($couponResult->status) {
                        $discount = $couponResult->couponDiscount;
                    }
                }
                $subTotal = $price * $orderQty;
                $totalDiscount = $discount * $orderQty;
                $total = $subTotal - $totalDiscount;

                // Execute closure if provided
                if ($closure) {
                    $closure($product->id, $price, $orderQty, $total, $totalDiscount, $subTotal);
                }

                // If Process is true, reduce product quantity
                if ($process) {
                    $product->decrement('quantity', $orderQty);
                }

                // Update order summary variables
                $totalQty += $availableQty;
                $orderTotal += $total;

                $orderSubTotal += $subTotal;
                $orderDiscount += $totalDiscount;

                // Fetch product details and add to products array
                $productDetails = $productService->getProductDetail($product->id);
                $productDetails['price'] = (float) $price;
                $productDetails['ordered_qty'] = (float) $orderQty;
                $productDetails['total_discount'] = (float) $totalDiscount;
                $products[] = $productDetails;
            }
        }

        if ($couponIsForOrder && $couponCode) {
            $orderDiscount = $couponService->isOrderEligbleForCoupon($couponCode, $orderTotal);
            $orderTotal = $orderTotal - $orderDiscount;
        }
        // Return order summary details or error status
        return (object) [
            'error' => $error,
            'orderTotal' => $orderTotal,
            'orderSubTotal' => $orderSubTotal,
            'orderDiscount' => $orderDiscount,
            'products' => $products,
        ];
    }

    /**
     * Checks if an order is valid based on its products and user ID.
     *
     * @param array $orderProducts An array containing the order products.
     * @param int $userId The ID of the user placing the order.
     *
     * @return string|null Any error message related to the order's validity, or null if valid.
     */
    public static function orderIsValid($orderProducts, $userId)
    {
        $result = OrderService::getOrderSummary($orderProducts, userId: $userId);
        return ($result->error);
    }

    /**
     * Deletes an order along with associated products and reverts quantities.
     *
     * @param NotificationService $notificationService Instance of NotificationService.
     * @param UserService $userService Instance of UserService.
     * @param int|string $idOrRef The ID or reference of the order.
     * @param bool $chargeUser Flag to determine if the user should be charged.
     * @param bool $isAdmin Flag indicating if the action is performed by an admin.
     * @param bool $isBot Flag indicating if the action is performed by a bot.
     *
     * @return bool
     */
    public static function deleteOrder(NotificationService $notificationService, UserService $userService, $idOrRef, $chargeUser = true, $isAdmin = true, $isBot = false)
    {

        $order = Order::where("id", $idOrRef)->orWhere("reference", $idOrRef)->with(["payments", "orderProducts" => ["product:id,name,selling_price"]])->first();
        if ($order) {
            $orderProducts = $order->orderProducts;
            // return qty
            $orderProducts->each(function ($orderProduct) {
                /**
                 * @var Product
                 */
                $product = Product::where("id", $orderProduct['product_id'])->first();
                $product->increment("quantity", $orderProduct['qty']);
                $orderProduct->delete();

                // Delete the image key from the product so we don't save it in deleted items
                unset($orderProduct->product->images);
            });

            $userId = null;
            $adminId = null;
            if (!$isBot) {
                if ($isAdmin) {
                    $adminId = Auth::guard("admin")->user()->id;
                } else {
                    $userId = Auth::guard("user")->user()->id;
                }
            }
            $order->delete();
            return true;

        } else {
            return false;
        }
    }

}

