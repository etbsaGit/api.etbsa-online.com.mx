<?php

namespace App\Http\Controllers\Api;

use App\Models\PostDoc;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Http\Controllers\ApiController;
use Illuminate\Support\Facades\Storage;

class PostDocController extends ApiController
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        return $this->respond(PostDoc::get());
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        //
    }

    /**
     * Display the specified resource.
     */
    public function show(PostDoc $postDoc)
    {
        // Usamos el método findOrFail para recuperar el PostDoc por su ID
        // $postDoc = PostDoc::findOrFail($postDoc);

        // Devolvemos la respuesta
        return $this->respond($postDoc);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, PostDoc $postDoc)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy($postDoc)
    {
        $postDoc = PostDoc::findOrFail($postDoc);
        Storage::disk('s3')->delete($postDoc->path);
        $postDoc->delete();
        return $this->respondSuccess();
    }
}
