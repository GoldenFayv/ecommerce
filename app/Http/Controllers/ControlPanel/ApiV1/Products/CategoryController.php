<?php

namespace App\Http\Controllers\ControlPanel\ApiV1\Products;

use App\Http\Controllers\Controller;
use App\Models\product\Category;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

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
    public function store(Request $request)
    {
        $validatedData = $request->validate([
            "name" => "required|string",
            "image" => "required|image",
        ]);
        $image = $request["image"];

        $filename = $this->uploadFile($image, "category");
        $validatedData["filename"] = $filename;
        $validatedData["slug"] = str()->slug($validatedData["name"]);
        $validatedData["admin_id"] = auth("admin")->user()->id;
        Category::create($validatedData);

        return $this->successResponse("Category Created");
    }
    public function update(Request $request, $id)
    {
        /**
         * @var Category
         */
        $category = Category::where("id", $id)
            ->orWhere("slug", $id)->first();

        if (!$category) {
            return $this->failureResponse("Category Not Found", status: 404);
        }
        if (isset($request["name"])) {
            $category->name = $request['name'];
        }
        if (isset($request["slug"])) {
            $category->slug = $request['slug'];
        }
        if (isset($request["description"])) {
            $category->description = $request['description'];
        }
        if (isset($request["image"]) && !empty($request["image"])) {
            try {
                Storage::delete("uploads/category/" . $category->filename);
                $filename = $this->uploadFile($request["image"], "category");
                $category->filename = $filename;
            } catch (\Throwable $th) {
                //throw $th;
            }
        }
        $category->save();
        return $this->successResponse("Category Updated");
    }
    public function destory($id)
    {
        /**
         * @var Category
         */
        $category = Category::where("id", $id)
            ->orWhere("slug", $id)->first();

        if ($category) {
            $category->delete();
            $this->successResponse("Category Deleted");
        } else {
            return $this->failureResponse("Category Not Found", status: 404);
        }
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
