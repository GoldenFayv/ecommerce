<?php

namespace App\Http\Controllers\ControlPanel\ApiV1\Products;

use App\Http\Controllers\Controller;
use App\Models\admin\Admin;
use App\Models\product\Product;
use App\Models\product\ProductImage;
use App\Models\ProductPrice;
use App\Services\ProductServices;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

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
    public function store(Request $request)
    {

        $validatedData = $request->validate([
            'name' => 'required|string',
            'selling_price' => 'required|numeric',
            'original_price' => 'required|numeric',
            'category_id' => 'required|exists:categories,id',
            'sub_category_id' => 'required|exists:sub_categories,id',
            'description' => 'required|string',
            'delivery_cost' => 'required|numeric',
            'quantity' => 'required|integer',
            'discount_percent' => 'required|numeric',
            "images.*" => "image",
        ]);

        /**
         * @var Admin
         */
        $admin = Auth::guard("admin")->user();
        $is_active = $admin->isSuperAdmin() ? 1 : 0;

        $validatedData["admin_id"] = $admin->id;
        $validatedData["is_active"] = $is_active;
        $validatedData["slug"] = str($validatedData["name"])->slug();
        $product = Product::create($validatedData);


        $images = $request["images"];
        if (!empty($images)) {

            foreach ($images as $image) {
                $filename = $this->uploadFile($image, "products/images");
                ProductImage::create([
                    "product_id" => $product->id,
                    "filename" => $filename
                ]);
            }
        }
        return $this->successResponse("Product Created", status: 201);
    }
    // public function update(Request $request, $idorslug)
    // {
    //     $product = Product::where("id", $idorslug)
    //         ->orWhere("slug", $idorslug)->first();
    //     if (!$product) {
    //         return $this->failureResponse("Product Not Found", status: 404);
    //     }

    //     if (!empty($request["name"]))
    //         $product->name = $request["name"];

    //     if (!empty($request["crate_price"]))
    //         $product->crate_price = $request["crate_price"];

    //     if (!empty($request["bottle_price"]))
    //         $product->bottle_price = $request["bottle_price"];

    //     if (!empty($request["category_id"]))
    //         $product->category_id = $request["category_id"];

    //     if (!empty($request["sub_category_id"]))
    //         $product->sub_category_id = $request["sub_category_id"];

    //     if (!empty($request["brand_id"]))
    //         $product->brand_id = $request["brand_id"];

    //     if (!empty($request["slug"]))
    //         $product->slug = $request["slug"];

    //     if (!empty($request["expiry_date"]))
    //         $product->expiry_date = $request["expiry_date"];

    //     if (!empty($request["description"]))
    //         $product->description = $request["description"];

    //     if (!empty($request["delivery_cost"]))
    //         $product->delivery_cost = $request["delivery_cost"];

    //     if (!empty($request["sku"]))
    //         $product->sku = $request["sku"];

    //     if (!empty($request["tax_percent"]))
    //         $product->tax_percent = $request["tax_percent"];

    //     if (isset($request["available_bottles"]))
    //         $product->available_bottles = $request["available_bottles"];

    //     if (isset($request["crate_type_id"])) {
    //         $crate_type = $request["crate_type_id"];
    //         if ($crate_type == 0) {
    //             $product->crate_type_id = null;
    //         } else {
    //             $product->crate_type_id = $request["crate_type_id"];
    //         }
    //     }
    //     if (isset($request["available_crates"]))
    //         $product->available_crates = $request["available_crates"];

    //     if (isset($request["status"]))
    //         $product->is_active = $request["status"];

    //     if (!empty($request["min_qty_threshold"]))
    //         $product->min_qty_threshold = $request["min_qty_threshold"];

    //     if (!empty($request["max_qty_threshold"]))
    //         $product->max_qty_threshold = $request["max_qty_threshold"];

    //     if (!empty($request["discount_percent"]))
    //         $product->discount_percent = $request["discount_percent"];

    //     if (isset($request["qty_per_crate"]))
    //         $product->qty_per_crate = $request["qty_per_crate"];

    //     $prices = $request["prices"];
    //     if (!empty($prices)) {
    //         foreach ($prices as $price) {
    //             $productPrice = ProductPrice::where(["product_id" => $product->id, "account_type_id" => $price['account_type_id']])->first();
    //             if ($productPrice) {
    //                 $productPrice->update([
    //                     "required_qty" => $price['required_qty'],
    //                     "price" => $price['price'],
    //                 ]);
    //             } else {
    //                 ProductPrice::create([
    //                     "product_id" => $product->id,
    //                     "required_qty" => $price['required_qty'],
    //                     "price" => $price['price'],
    //                     "account_type_id" => $price["account_type_id"]
    //                 ]);
    //             }

    //         }
    //     }

    //     $product->save();

    //     return $this->successResponse("Product Updated");
    // }
    public function destory($idorslug)
    {
        /**
         * @var Product
         */
        $product = Product::where("id", $idorslug)
            ->orWhere("slug", $idorslug)->first();

        if ($product) {
            $product->delete();
            $this->successResponse("Product Deleted");
        } else {
            return $this->failureResponse("Product Not Found", status: 404);
        }
    }

    // FUNCTIONS
    public function generateSKU($product_name, $product_qty, $product_unit_crate)
    {
        $sku = strtoupper(substr($product_name, 0, 3) . '-' . $product_qty * $product_unit_crate . '-' . rand(000, 999));
        return $sku;
    }
}
