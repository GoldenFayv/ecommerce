<?php

namespace App\Http\Controllers\ControlPanel\ApiV1\Products;

use App\Http\Controllers\Controller;
use App\Models\product\ProductImage;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class ProductImageController extends Controller
{
    public function index(Request $request)
    {
        $request->validate([
            'product_id' => "required|exists:products,id"
        ]);

        $product_id = $request['product_id'];
        $images = ProductImage::where("product_id", $product_id)->get();
        $result = array_map(function ($image) {
            return [
                "id" => $image['id'],
                "url" => Storage::url("uploads/products/images/" . $image['filename']),
            ];
        }, $images->toArray());
        return $this->successResponse("", $result);
    }
    public function store(Request $request)
    {
        $request->validate([
            "product_id" => "exists:products,id",
            "images" => "array",
            "images.*" => "image|max:1024",
        ]);
        // Update Images
        $images = $request["images"];
        $uploaded_images = [];
        if (!empty($images)) {
            foreach ($images as $image) {
                $filename = $this->uploadFile($image, "products/images");
                $image = ProductImage::create([
                    "product_id" => $request['product_id'],
                    "filename" => $filename
                ]);
                $uploaded_images[] = $image;
            }
        }
        $result = array_map(function ($image) {
            return [
                "id" => $image['id'],
                "url" => Storage::url("uploads/products/images/" . $image['filename']),
            ];
        }, $uploaded_images);

        return $this->successResponse("Image Added", $result);
    }
    public function destroy($image_id)
    {
        /**
         * @var ProductImage
         */
        $product_image = ProductImage::where("id", $image_id)->first();

        if ($product_image) {
            // check if this is the only image of this product
            $count = ProductImage::where("product_id", $product_image->product_id)->count();
            if ($count > 1) {
                Storage::delete("uploads/products/images/" . $product_image->filename);
                $product_image->delete();
                return $this->successResponse("Product Image Deleted");
            } else {
                return $this->failureResponse("Product Must Have atleast one image");
            }
        } else {
            return $this->failureResponse("Product Image Not Found", status: 404);
        }
    }
}
