<?php

namespace App\Http\Controllers\Api\V1\Product;

use App\Http\Controllers\Controller;
use App\Models\product\Category;

class CategoryController extends Controller
{
    public function index()
    {
        $cateIds = Category::pluck("id");

        $result = array_map(function ($id) {
            return $this->getDetail($id);
        }, $cateIds->toArray());

        return $this->successResponse("", $result);
    }
    public function show($idorslug)
    {
        $category = $this->getDetail($idorslug);
        if (!$category) {
            return $this->failureResponse("Category Not Found", status: 404);
        }
        return $this->successResponse("", $category);
    }

    public function getDetail($idorslug)
    {
        $category = Category::where("slug", $idorslug)
            ->orWhere("id", $idorslug)->first();

        if ($category) {
            return [
                "id" => $category->id,
                "name" => $category->name,
                "slug" => $category->slug,
                "image" => $category->image,
                "sub_categories" => $category->sub_categories->map(fn($sub_category) => [
                    "name" => $sub_category->name,
                    "slug" => $sub_category->slug,
                    "image" => $sub_category->image,
                ])
            ];
        }
    }
}
