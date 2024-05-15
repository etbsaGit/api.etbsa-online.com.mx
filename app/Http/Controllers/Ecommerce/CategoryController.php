<?php

namespace App\Http\Controllers\Ecommerce;

use Illuminate\Support\Str;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use App\Models\Ecommerce\Category;
use Illuminate\Support\Facades\Storage;
use App\Http\Controllers\ApiController;
use App\Repositories\CategoryRepository;
use App\Http\Requests\Ecommerce\StoreCategoryRequest;
use App\Http\Requests\Ecommerce\UpdateCategoryRequest;

class CategoryController extends ApiController
{

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

    private function saveImage($base64, $defaultPathFolder)
    {
        // Check if image is valid base64 string
        if (preg_match('/^data:image\/(\w+);base64,/', $base64, $type)) {
            // Take out the base64 encoded text without mime type
            $image = substr($base64, strpos($base64, ',') + 1);
            // Get file extension
            $type = strtolower($type[1]); // jpg, png, gif

            // Check if file is an image
            if (!in_array($type, ['jpg', 'jpeg', 'gif', 'png'])) {
                throw new \Exception('invalid image type');
            }
            $image = str_replace(' ', '+', $image);
            $image = base64_decode($image);

            if ($image === false) {
                throw new \Exception('base64_decode failed');
            }
        } else {
            throw new \Exception('did not match data URI with image data');
        }

        $fileName = Str::random() . '.' . $type;
        $filePath = $defaultPathFolder . '/' . $fileName;

        // Guardar el archivo en AWS S3
        Storage::disk('s3')->put($filePath, $image);

        return $filePath;
    }
}
