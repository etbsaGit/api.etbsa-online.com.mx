<?php

namespace App\Http\Controllers\Intranet;

use App\Http\Controllers\ApiController;
use App\Http\Requests\Intranet\Products\ProductRiegoRequest;
use App\Models\Intranet\Currency;
use App\Models\Intranet\NivelPartner;
use App\Models\Intranet\ProductBrand;
use App\Models\Intranet\ProductCategory;
use App\Models\Intranet\ProductsRiego;
use App\Models\Intranet\ProductSubCategory;
use App\Models\Intranet\ProductSupplier;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ProductRiegoController extends ApiController
{
    public function index(Request $request)
    {
        $filters = $request->all();

        $products = ProductsRiego::with([
            'marca',
            'proveedor',
            'categoria',
            'subcategoria',
            'currency',
            'precios.nivelPartner'
        ])->filter($filters)->paginate(10);

        return $this->respond($products, 'Lista de productos cargada');
    }

    public function store(ProductRiegoRequest $request)
    {
        DB::beginTransaction();
        try {
            // crear producto
            $product = ProductsRiego::create($request->validated());

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
                $product->load('precios'),
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
            'precios.nivelPartner'
        ]);

        return $this->respond($productRiego, 'Detalle del producto');
    }

    public function update(ProductRiegoRequest $request, ProductsRiego $productRiego)
    {
        DB::beginTransaction();
        try {
            // actualizar producto
            $productRiego->update($request->validated());
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
                $productRiego->load('precios'),
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
            // eliminar producto
            $productRiego->delete();
            DB::commit();
            return $this->respondSuccess('Producto eliminado correctamente');
        } catch (\Exception $e) {
            DB::rollBack();
            throw $e;
        }
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
            'categorias' => $riegoCategory ? ProductCategory::where('id', $riegoCategoryId)->get() : ProductCategory::all(),
            'subcategorias' => $riegoCategoryId ? ProductSubCategory::where('category_id', $riegoCategoryId)->get() : ProductSubCategory::all(),
            'monedas' => Currency::all(),
        ];
        return $this->respond($data);
    }
}
