<?php

namespace App\Http\Controllers\Api\V1\Product;

use App\Http\Controllers\Controller;
use App\Models\product\SubCategory;
use Illuminate\Http\Request;

class SubCategoryController extends Controller
{

    public function index(Request $request)
    {
        $cateIds = SubCategory::pluck("id");
        if (isset($request['category_id']) && !empty($request['category_id'])) {
            $cateIds = SubCategory::where("category_id", $request['category_id'])->pluck("id");
        }

        $result = array_map(function ($id) {
            return $this->getDetail($id);
        }, $cateIds->toArray());

        return $this->successResponse("", $result);
    }
    public function show($idorslug)
    {
        $subcategory = $this->getDetail($idorslug);
        if (!$subcategory) {
            return $this->failureResponse("SubCategory Not Found", status: 404);
        }
        return $this->successResponse("", $subcategory);
    }

    public function getDetail($idorslug)
    {
        $sub_category = SubCategory::where("slug", $idorslug)
            ->with(['category'])
            ->orWhere("id", $idorslug)->first();

        if ($sub_category) {
            return [
                "id" => $sub_category->id,
                "name" => $sub_category->name,
                "slug" => $sub_category->slug,
                "image" => $sub_category->image,
                "category" => [
                    "name" => $sub_category->category->name,
                    "slug" => $sub_category->category->slug,
                    "image" => $sub_category->category->image,
                ]
            ];
        }
    }

}
