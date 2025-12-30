<?php

namespace App\Http\Controllers;

use Illuminate\Routing\Controller;

class ApiController extends Controller
{
    /**
     * Respuesta genérica OK
     */
    protected function respond($data = null, string $message = '', int $statusCode = 200, array $headers = [])
    {
        return response()->json([
            'success' => $statusCode < 400,
            'message' => $message,
            'data'    => $data
        ], $statusCode, $headers);
    }

    /**
     * 200 OK sin data
     */
    protected function respondSuccess(string $message = 'Operación realizada correctamente')
    {
        return $this->respond(null, $message, 200);
    }

    /**
     * 201 Created
     */
    protected function respondCreated($data, string $message = 'Registro creado correctamente')
    {
        return $this->respond($data, $message, 201);
    }

    /**
     * Error genérico
     */
    protected function respondError(string $message, int $statusCode, array $errors = [])
    {
        return response()->json([
            'success' => false,
            'message' => $message,
            'errors'  => $errors
        ], $statusCode);
    }

    /**
     * 401
     */
    protected function respondUnauthorized(string $message = 'No autorizado')
    {
        return $this->respondError($message, 401);
    }

    /**
     * 403
     */
    protected function respondForbidden(string $message = 'Acceso denegado')
    {
        return $this->respondError($message, 403);
    }

    /**
     * 404
     */
    protected function respondNotFound(string $message = 'Recurso no encontrado')
    {
        return $this->respondError($message, 404);
    }
}
