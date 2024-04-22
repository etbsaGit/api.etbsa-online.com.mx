<?php

namespace App\Http\Controllers\Ecommerce;

use App\Http\Controllers\ApiController;
use App\Http\Requests\Ecommerce\StoreCategoryRequest;
use App\Http\Requests\Ecommerce\UpdateCategoryRequest;
use App\Models\Ecommerce\Category;
use App\Repositories\CategoryRepository;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class CategoryController extends ApiController
{

    private CategoryRepository $categoryRepository;

    public function __construct(CategoryRepository $categoryRepository)
    {
        $this->categoryRepository = $categoryRepository;
    }
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $items = $this->categoryRepository->index(request()->all());
        return $this->respond(compact('items'));
    }

    /**
     * Store a newly created resource in storage.
     */
    // public function store(StoreCategoryRequest $request) : JsonResponse
    public function store(StoreCategoryRequest $request)
    {
        $payload = $request->validated();

        $category = $this->categoryRepository->createCategory($payload);

        return $this->respondCreated([
            'success' => true,
            'message' => 'Categoria Creada',
            'data' => $category
        ]);
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
        $payload = $request->validated();

        $updated = $this->categoryRepository->updateCategory($category->id, $payload);

        return $this->respondCreated([
            'success' => true,
            'message' => 'Categoria Creada',
            'data' => $updated
        ]);
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
