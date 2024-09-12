<?php

namespace App\Http\Controllers\ControlPanel\ApiV1\Products;

use App\Http\Controllers\Controller;
use App\Models\product\SubCategory;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;

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
    public function store(Request $request)
    {
        $validatedData = $request->validate([
            "name" => "required|string",
            "category_id" => "required|exists:categories,id",
            "image" => "required|image",
        ]);
        $image = $request["image"];

        $filename = $this->uploadFile($image, "subcategory");
        $validatedData["filename"] = $filename;
        $validatedData["slug"] = str()->slug($validatedData["name"]);
        $validatedData["admin_id"] = auth("admin")->user()->id;
        SubCategory::create($validatedData);

        return $this->successResponse("SubCategory Created", status: 201);
    }
    public function update(Request $request, $idorslug)
    {
        /**
         * @var SubCategory
         */
        $subcategory = SubCategory::where("id", $idorslug)
            ->orWhere("slug", $idorslug)->first();
        if (!$subcategory) {
            return $this->failureResponse("SubCategory Not Found", status: 404);
        }
        if (isset($request["name"]) && !empty($request["name"])) {
            $subcategory->name = $request['name'];
        }
        if (isset($request["slug"]) && !empty($request["slug"])) {
            $subcategory->slug = $request['slug'];
        }
        if (isset($request["description"]) && !empty($request["description"])) {
            $subcategory->description = $request['description'];
        }
        if (isset($request["category_id"]) && !empty($request["category_id"])) {
            $subcategory->category_id = $request['category_id'];
        }
        if (isset($request["image"]) && !empty($request["image"])) {
            Storage::delete("uploads/subcategory/" . $subcategory->filename);
            $filename = $this->uploadFile($request["image"], "subcategory");
            $subcategory->filename = $filename;
        }
        $subcategory->save();
        return $this->successResponse("SubCategory Updated");
    }
    public function destory($idorslug)
    {
        /**
         * @var SubCategory
         */
        $product = SubCategory::where("id", $idorslug)
            ->orWhere("slug", $idorslug)->first();

        if ($product) {
            $product->delete();
            $this->successResponse("SubCategory Deleted");
        } else {
            return $this->failureResponse("SubCategory Not Found", status: 404);
        }
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
