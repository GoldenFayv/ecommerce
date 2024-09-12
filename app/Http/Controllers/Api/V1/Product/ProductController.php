<?php

namespace App\Http\Controllers\Api\V1\Product;

use App\Http\Controllers\Controller;
use App\Models\product\Product;
use App\Services\ProductServices;

class ProductController extends Controller
{
    public function index()
    {

        $productIds = Product::where("is_active", 1)->pluck("id");
        if ($productIds) {
            $result = array_map(function ($id) {
                $productServices = new ProductServices;
                return $productServices->getProductDetail($id);
            }, $productIds->toArray());
        } else {
            $result = null;
        }
        return $this->successResponse("", $result);
    }

    public function show(ProductServices $productServices, $idorslug)
    {
        $product = $productServices->getProductDetail($idorslug);
        if (!$product) {

            return $this->failureResponse("Product Not Found", status: 404);
        }
        return $this->successResponse("", $product);
    }


    // FUNCTIONS
}
