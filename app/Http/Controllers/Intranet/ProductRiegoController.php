<?php

namespace App\Http\Controllers\Intranet;

use App\Http\Controllers\ApiController;
use App\Http\Requests\Intranet\Products\ProductRiegoRequest;
use App\Models\Intranet\Currency;
use App\Models\Intranet\NivelPartner;
use App\Models\Intranet\ProductBrand;
use App\Models\Intranet\ProductCategory;
use App\Models\Intranet\ProductRiegoImage;
use App\Models\Intranet\ProductsRiego;
use App\Models\Intranet\ProductSubCategory;
use App\Models\Intranet\ProductSupplier;
use App\Traits\UploadableFile;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

class ProductRiegoController extends ApiController
{
    use UploadableFile;
    public function index(Request $request)
    {
        $filters = $request->all();

        $perPage = $request->input('per_page', 12);

        $products = ProductsRiego::with([
            'marca',
            'proveedor',
            'categoria',
            'subcategoria',
            'currency',
            'precios.nivelPartner',
            'imagenes'
        ])->filter($filters)->paginate($perPage);

        return $this->respond($products, 'Lista de productos cargada');
    }

    public function store(ProductRiegoRequest $request)
    {
        DB::beginTransaction();
        try {
            // crear producto
            $product = ProductsRiego::create($request->validated());

            // imagenes
            if ($request->imagenes) {
                foreach ($request->imagenes as $imgBase64) {
                    if ($imgBase64) {
                        $relativePath = $this->saveImage($imgBase64, $product->default_path_folder);
                        $product->imagenes()->create(['image_url' => $relativePath]);
                    }
                }
            }

            // guardar precios
            if ($request->has('precios')) {
                $precios = collect($request->precios)->map(function ($p) use ($product) {
                    return [
                        'nivel_partner_id' => $p['nivel_partner_id'],
                        'precio' => $p['precio'],
                        'currency_id' => $product->currency_id
                    ];
                });
                $product->precios()->createMany($precios);
            }
            DB::commit();

            return $this->respondCreated(
                $product->load('precios', 'imagenes'),
                'Producto creado correctamente'
            );
        } catch (\Exception $e) {
            DB::rollBack();
            throw $e;
        }
    }

    public function show(ProductsRiego $productRiego)
    {
        $productRiego->load([
            'marca',
            'proveedor',
            'categoria',
            'subcategoria',
            'currency',
            'precios.nivelPartner',
            'imagenes'
        ]);

        return $this->respond($productRiego, 'Detalle del producto');
    }

    public function update(ProductRiegoRequest $request, ProductsRiego $productRiego)
    {
        DB::beginTransaction();
        try {
            // actualizar producto
            $productRiego->update($request->validated());
            if ($request->has('imagenes') && is_array($request->imagenes)) {
                foreach ($request->imagenes as $imgBase64) {
                    if ($imgBase64) {
                        $relativePath = $this->saveImage($imgBase64, $productRiego->default_path_folder);
                        $productRiego->imagenes()->create(['image_url' => $relativePath]);
                    }
                }
            }
            // reemplazar precios
            if ($request->has('precios')) {
                // eliminar precios actuales
                $productRiego->precios()->delete();
                // insertar los nuevos
                $precios = collect($request->precios)->map(function ($p) use ($productRiego) {
                    return [
                        'nivel_partner_id' => $p['nivel_partner_id'],
                        'precio' => $p['precio'],
                        'currency_id' => $productRiego->currency_id
                    ];
                });
                $productRiego->precios()->createMany($precios);
            }
            DB::commit();
            return $this->respond(
                $productRiego->load('precios', 'imagenes'),
                'Producto actualizado correctamente'
            );
        } catch (\Exception $e) {
            DB::rollBack();
            throw $e;
        }
    }

    public function destroy(ProductsRiego $productRiego)
    {
        DB::beginTransaction();
        try {
            // eliminar precios
            $productRiego->precios()->delete();
            // eliminar imagenes
            foreach ($productRiego->imagenes as $img) {
                if ($img->image_url) {
                    Storage::disk('s3')->delete($img->image_url);
                }
            }
            $productRiego->imagenes()->delete();
            // eliminar producto
            $productRiego->delete();
            DB::commit();
            return $this->respondSuccess('Producto eliminado correctamente');
        } catch (\Exception $e) {
            DB::rollBack();
            throw $e;
        }
    }

    public function destroyImage(ProductRiegoImage $image)
    {
        if ($image->image_url) {
            Storage::disk('s3')->delete($image->image_url);
        }
        $image->delete();
        return $this->respondSuccess('Imagen eliminada correctamente');
    }

    public function getOptions()
    {
        $riegoCategory = ProductCategory::where('name', 'RIEGO')->first();
        $riegoCategoryId = $riegoCategory ? $riegoCategory->id : null;

        $data = [
            'proveedores' => ProductSupplier::all(),
            'marcas' => ProductBrand::all(),
            'nivelesPartner' => NivelPartner::all(),
            'currencies' => Currency::all(),
            'categorias' => $riegoCategory ? ProductCategory::where('id', $riegoCategoryId)->first() : ProductCategory::all(),
            'subcategorias' => $riegoCategoryId ? ProductSubCategory::where('category_id', $riegoCategoryId)->get() : ProductSubCategory::all(),
            'monedas' => Currency::all(),
        ];
        return $this->respond($data);
    }
}
