<?php

namespace App\Services;

use App\Models\product\Product;
use App\Models\sale\OrderProduct;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Storage;

class ProductServices
{
    public function __construct()
    {
    }

    public function getProductDetail($idorslug, $isAdmin = false)
    {
        $product = Product::where("id", $idorslug)->first();

        if ($product) {
            $images = $isAdmin ? $product->images->map(fn($image) => [
                "id" => $image['id'],
                "url" => $image->url
            ]) : $product->images->map(fn($image) => $image->url);

            return [
                "id" => $product->id,
                "name" => $product->name,
                "slug" => $product->slug,
                "status" => $product->status,
                "is_active" => $product->is_active,
                "selling_price" => $product->selling_price,
                "original_price" => $product->original_price,
                "description" => $product->description,
                "delivery_cost" => $product->delivery_cost,
                "quantity" => $product->quantity,
                "discount_percent" => $product->discount_percent,
                "category" => [
                    "id" => $product->category_id,
                    "slug" => $product->category->slug,
                    "name" => $product->category->name,
                ],
                "sub_category" => $product->sub_category ? [
                    "id" => $product->sub_category_id,
                    "slug" => $product->sub_category->slug,
                    "name" => $product->sub_category->name,
                ] : null,
                "images" => $images
            ];
        }
    }

    public function watchQuantity($product)
    {
        $min_thresh = (int) $product['min_qty_threshold'];
        $max_thresh = (int) $product['max_qty_threshold'];
        $qty = (int) $product['available_crates'];

        if ($qty < $min_thresh) {
        }
    }
}
