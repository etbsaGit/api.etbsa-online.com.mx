<?php

namespace App\Http\Controllers\Api;

use App\Models\Suggestion;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use App\Http\Controllers\ApiController;
use App\Http\Requests\Suggestion\StoreRequest;

class SuggestionController extends ApiController
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $filters = $request->all();
        $user = Auth::user();

        if ($user->hasRole('Admin') || $user->hasRole('RRHH')) {
            $suggestion = Suggestion::filter($filters)
                ->with(['user.empleado', 'estatus'])
                ->orderBy('updated_at', 'desc')
                ->paginate(10);
        } else {
            $suggestion = Suggestion::filter($filters)
                ->where('user_id', $user->id)
                ->with(['user.empleado', 'estatus'])
                ->orderBy('updated_at', 'desc')
                ->paginate(10);
        }

        return $this->respond($suggestion);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StoreRequest $request)
    {
        $suggestion = Suggestion::create($request->validated());

        return $this->respondCreated($suggestion);
    }

    /**
     * Display the specified resource.
     */
    public function show(Suggestion $suggestion)
    {
        return $this->respond($suggestion->load('user', 'estatus'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(StoreRequest $request, Suggestion $suggestion)
    {
        $suggestion->update($request->validated());

        return $this->respond($suggestion);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Suggestion $suggestion)
    {
        $suggestion->delete();

        return $this->respondSuccess();
    }

    public function getOnlyTrashed(Request $request)
    {
        $filters = $request->all();

        $suggestions = Suggestion::filter($filters)->with(['user.empleado', 'estatus'])->onlyTrashed()->paginate(10);

        return $this->respond($suggestions);
    }
}
