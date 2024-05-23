<?php

namespace App\Http\Controllers\Ecommerce;


use App\Traits\UploadableFile;
use App\Models\Ecommerce\Category;
use App\Http\Controllers\ApiController;
use Illuminate\Support\Facades\Storage;
use App\Http\Requests\Ecommerce\StoreCategoryRequest;
use App\Http\Requests\Ecommerce\UpdateCategoryRequest;

class CategoryController extends ApiController
{
    use UploadableFile;
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        return response()->json(Category::whereNull('parent_id')->with('childrenRecursive')->get());
    }


    /**
     * Store a newly created resource in storage.
     */
    // public function store(StoreCategoryRequest $request) : JsonResponse
    public function store(StoreCategoryRequest $request)
    {
        $category = Category::create($request->only(['name', 'slug']));

        if (!is_null($request['base64'])) {
            $relativePath  = $this->saveImage($request['base64'], $category->default_path_folder);
            $request['base64'] = $relativePath;
            $updateData = ['logo' => $relativePath];
            $category->update($updateData);
        }
        return response()->json($category);
    }

    /**
     * Display the specified resource.
     */
    public function show(Category $category)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdateCategoryRequest $request, Category $category)
    {
        $category->update($request->validated());

        if (!is_null($request['base64'])) {
            if ($category->logo) {
                Storage::disk('s3')->delete($category->logo);
            }
            $relativePath  = $this->saveImage($request['base64'], $category->default_path_folder);
            $request['base64'] = $relativePath;
            $updateData = ['logo' => $relativePath];
            $category->update($updateData);
        }

        return response()->json($category);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Category $category)
    {
        // $this->categoryRepository->delete($category->id);
        $category->delete();
        return $this->respondSuccess();
    }

}
