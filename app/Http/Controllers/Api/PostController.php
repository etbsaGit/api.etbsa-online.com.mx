<?php

namespace App\Http\Controllers\Api;

use App\Models\Post;
use App\Models\Linea;
use App\Models\Puesto;
use App\Models\Estatus;
use App\Models\PostDoc;
use App\Models\Sucursal;
use App\Models\Departamento;
use Illuminate\Http\Request;
use App\Traits\UploadableFile;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use App\Http\Controllers\ApiController;
use App\Http\Requests\Post\PutPostRequest;
use App\Http\Requests\Post\StorePostRequest;

class PostController extends ApiController
{
    use UploadableFile;
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        return $this->respond(Post::with('user', 'user.empleado', 'linea', 'sucursal', 'departamento', 'puesto', 'estatus', 'postDoc')->get());
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StorePostRequest $request)
    {
        $post = Post::create($request->validated());
        $docs = $request->archivos;

        if (!empty($docs)) {
            foreach ($docs as $doc) {
                $postDoc = PostDoc::create(['post_id' => $post->id, 'name' => $doc['name'], 'extension' => $doc['extension']]);
                $relativePath  = $this->saveDoc($doc['base64'], $postDoc->default_path_folder);
                $updateData = ['path' => $relativePath];
                $postDoc->update($updateData);
            }
        }

        return $this->respond($post);
    }

    /**
     * Display the specified resource.
     */
    public function show(Post $post)
    {
        return $this->respond($post);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(PutPostRequest $request, Post $post)
    {
        $post->update($request->validated());
        $docs = $request->archivos;

        if (!empty($docs)) {
            foreach ($docs as $doc) {
                $postDoc = PostDoc::create(['post_id' => $post->id, 'name' => $doc['name'], 'extension' => $doc['extension']]);
                $relativePath  = $this->saveDoc($doc['base64'], $postDoc->default_path_folder);
                $updateData = ['path' => $relativePath];
                $postDoc->update($updateData);
            }
        }

        return $this->respond($post);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Post $post)
    {
        $post->postDoc()->delete();

        $post->delete();

        return response()->json(['message' => 'Post y registros relacionados eliminados con éxito'], 200);
    }


    public function getforms()
    {
        $data = [
            'sucursales' => Sucursal::all(),
            'departamentos' => Departamento::all(),
            'lineas' => Linea::all(),
            'puestos' => Puesto::all(),
            'estatus' => Estatus::where('tipo_estatus', 'post')->get()
        ];

        return $this->respond($data);
    }

    public function getAll(Request $request)
    {
        $user = Auth::user();
        $filters = $request->all();

        // if ($user && !$user->roles()->where('name', 'Corporativo')->exists()) {
        //     $filters['linea_id'] = $user->empleado->linea->id;
        //     $filters['departamento_id'] = $user->empleado->departamento->id;
        // }

        if ($user && $user->empleado && $user->empleado->sucursal()->where('nombre', 'Corporativo')->doesntExist()) {
            $filters['linea_id'] = $user->empleado->linea->id;
            $filters['departamento_id'] = $user->empleado->departamento->id;
        }

        $allPosts = Post::filter($filters)
            ->with('user', 'user.empleado', 'linea', 'sucursal', 'departamento', 'puesto', 'estatus', 'postDoc')
            ->get();

        $postsWithNullRelations = Post::with('user', 'user.empleado', 'linea', 'sucursal', 'departamento', 'puesto', 'estatus', 'postDoc')
            ->whereNull('linea_id')
            ->whereNull('sucursal_id')
            ->whereNull('departamento_id')
            ->whereNull('puesto_id')
            ->get();

            $posts = $postsWithNullRelations->merge($allPosts)->sortByDesc('updated_at')->values();

        $data = [
            'post' => $posts,
            'lineas' => Linea::all(),
            'sucursales' => Sucursal::all(),
            'departamentos' => Departamento::all(),
            'puestos' => Puesto::all(),
            'estatus' => Estatus::where('tipo_estatus', 'post')->get()
        ];
        return $this->respond($data);
    }

    public function getPerAuth()
    {
        $user = Auth::user();
        $posts = Post::where('user_id', $user->id)->with('user', 'user.empleado', 'linea', 'sucursal', 'departamento', 'puesto', 'estatus', 'postDoc')->get();
        return $this->respond($posts);
    }
}
