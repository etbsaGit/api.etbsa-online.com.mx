<?php

namespace App\Http\Controllers\Intranet;

use App\Http\Controllers\ApiController;
use Illuminate\Support\Facades\DB;
use App\Models\Intranet\Product;
use Illuminate\Http\Request;
use App\Http\Requests\Intranet\Products\ProductRequest;
use App\Models\Intranet\ProductBrand;
use App\Models\Intranet\ProductCategory;
use App\Models\Intranet\ProductSubCategory;
use App\Models\Intranet\ProductSupplier;
use App\Models\Sucursal;

class ProductController extends ApiController
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $filters = $request->all();

        $products = Product::with([
            'brand',
            'category.condicionesPago',
            'subcategory',
            'currency',
            'agency',
            'supplier',
            'precios.condicionPago'
        ])
            ->filter($filters)
            ->paginate(10);

        return $this->respond(
            $products,
            'Lista de productos cargada correctamente'
        );
    }


    /**
     * Store a newly created resource in storage.
     */
    public function store(ProductRequest $request)
    {
        DB::beginTransaction();
        try {
            // crear producto
            $product = Product::create($request->validated());
            // guardar precios
            if ($request->has('precios')) {
                $precios = collect($request->precios)->map(function ($p) use ($product) {
                    return [
                        'condicion_pago_id' => $p['condicion_pago_id'],
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

    /**
     * Display the specified resource.
     */
    public function show(Product $product)
    {
        $product->load([
            'brand',
            'category',
            'subcategory',
            'currency',
            'agency',
            'supplier',
            'currency'
        ]);

        return $this->respond(
            $product,
            'Detalle del producto'
        );
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(ProductRequest $request, Product $product)
    {
        DB::beginTransaction();
        try {
            // actualizar producto
            $product->update($request->validated());

            // reemplazar precios
            if ($request->has('precios')) {
                // eliminar los precios actuales
                $product->precios()->delete();
                // insertar los nuevos
                $precios = collect($request->precios)->map(function ($p) use ($product) {
                    return [
                        'condicion_pago_id' => $p['condicion_pago_id'],
                        'precio' => $p['precio'],
                        'currency_id' => $product->currency_id
                    ];
                });
                $product->precios()->createMany($precios);
            }
            DB::commit();
            return $this->respond(
                $product->load('precios'),
                'Producto actualziado correctamente'
            );
        } catch (\Exception $e) {
            DB::rollBack();
            throw $e;
        }
    }
    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Product $product)
    {
        DB::beginTransaction();

        try {
            // 1. borrar precios
            $product->precios()->delete();

            // 2. borrar producto
            $product->delete();

            DB::commit();

            return $this->respondSuccess(
                'Producto eliminado correctamente'
            );
        } catch (\Exception $e) {
            DB::rollBack();
            throw $e;
        }
    }

    // getOptions
    public function getOptions()
    {
        $data = [
            'proveedores' => ProductSupplier::all(),
            'sucursales' => Sucursal::all(),
            'categorias' => ProductCategory::with('condicionesPago')->get(),
            'subcategorias' => ProductSubCategory::all(),
            'marcas' => ProductBrand::all(),
        ];
        return $this->respond($data);
    }
}
