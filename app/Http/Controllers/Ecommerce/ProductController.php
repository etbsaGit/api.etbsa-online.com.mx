<?php

namespace App\Http\Controllers\Ecommerce;

use Illuminate\Support\Str;
use App\Models\Ecommerce\Brand;
use App\Models\Ecommerce\Vendor;
use App\Models\Ecommerce\Feature;
use App\Models\Ecommerce\Product;
use App\Contracts\ProductContract;
use App\Models\Ecommerce\Category;
use App\Models\Ecommerce\ProductImage;
use App\Http\Controllers\ApiController;
use Illuminate\Support\Facades\Storage;
use App\Http\Requests\Ecommerce\StoreProductRequest;
use App\Http\Requests\Ecommerce\UpdateProductRequest;

class ProductController extends ApiController
{
    private ProductContract $productRepository;

    public function __construct(ProductContract $productRepository)
    {
        $this->productRepository = $productRepository;
    }
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        return response()->json(Product::with('brand', 'vendor', 'images', 'features', 'categories')->get());
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StoreProductRequest $request)
    {
        $product = Product::create($request->only(['name','slug', 'sku', 'description', 'quantity', 'price', 'active', 'featured', 'brand_id', 'vendor_id','sale_price']));
        $features = $request->features;
        $category_id = $request->category_id;
        $images = $request->images;
        $product->features()->sync($features);
        $product->categories()->sync($category_id);

        if (!empty($images)) {
            foreach ($images as $image) {
                $productImage = ProductImage::create(['product_id' => $product->id]);
                $relativePath  = $this->saveImage($image, $productImage->default_path_folder);
                $updateData = ['path' => $relativePath];
                $productImage->update($updateData);
            }
        }

        return response()->json($product->load('brand', 'vendor', 'images', 'features', 'categories'));
    }

    /**
     * Display the specified resource.
     */
    public function show(Product $product)
    {
        return response()->json($product->load('brand', 'vendor', 'images', 'features', 'categories'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdateProductRequest $request, Product $product)
    {
        $product->update($request->only(['name', 'slug', 'sku', 'description', 'quantity', 'price', 'active', 'featured', 'brand_id', 'vendor_id', 'sale_price']));
        $features = $request->features;
        $images = $request->images;
        $category_id = $request->category_id;
        $product->features()->sync($features);
        $product->categories()->sync($category_id);

        if (!empty($images)) {

            foreach ($images as $image) {
                $productImage = ProductImage::create(['product_id' => $product->id]);
                $relativePath  = $this->saveImage($image, $productImage->default_path_folder);
                $updateData = ['path' => $relativePath];
                $productImage->update($updateData);
            }
        }

        return response()->json($product->load('brand', 'vendor', 'images', 'features', 'categories'));
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Product $product)
    {
        //
    }

    public function formProduct()
    {
        $data = [
            'categories' => Category::all(),
            'features' => Feature::all(),
            'brands' => Brand::all(),
            'vendors' => Vendor::all(),
        ];
        return $this->respond($data);
    }

    public function changeActive(Product $product)
    {
        if ($product->active == true) {
            $product->active = false;
        } elseif ($product->active == false) {
            $product->active = true;
        }

        $product->save();

        return response()->json(['mensaje' => 'Estado cambiado exitosamente']);
    }

    public function changeFeatured(Product $product)
    {
        if ($product->featured == true) {
            $product->featured = false;
        } elseif ($product->featured == false) {
            $product->featured = true;
        }

        $product->save();

        return response()->json(['mensaje' => 'Estado cambiado exitosamente']);
    }

    public function deleteImg(ProductImage $productImage)
    {
        Storage::disk('s3')->delete($productImage->path);
        $productImage->delete();
        return response('', 204);
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
