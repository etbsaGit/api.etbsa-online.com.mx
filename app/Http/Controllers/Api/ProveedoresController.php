<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\ApiController;
use App\Mail\Proveedores\DocumentoProveedorSubidoMail;
use App\Mail\Proveedores\DocumentoValidadoMail;
use App\Mail\Proveedores\NuevoProveedorMail;
use App\Models\Empleado;
use App\Models\Estatus;
use App\Models\Proveedores;
use App\Models\ProveedoresDocs;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;

class ProveedoresController extends ApiController
{
    /**
     * Determina si el usuario autenticado tiene permisos para editar la información
     * o subir/modificar documentos del proveedor.
     * Solo permitido para:
     * 1. Usuario que registró al proveedor (registered_by).
     * 2. Usuarios con rol 'Proveedores' o 'Admin'.
     */
    private function canUserManageProveedor(?User $user, Proveedores $proveedor): bool
    {
        if (!$user) {
            return false;
        }

        if ($user->hasRole('Proveedores') || $user->hasRole('Admin')) {
            return true;
        }

        if (!empty($proveedor->registered_by) && (int) $proveedor->registered_by === (int) $user->id) {
            return true;
        }

        return false;
    }

    /**
     * Estatus por defecto para documentos recién subidos: 'Por Validar' (tipo_estatus = 'proveedores-doc')
     */
    private function getEstatusPorValidarId(): int
    {
        $est = Estatus::where('tipo_estatus', 'proveedores-doc')
            ->where('nombre', 'Por Validar')
            ->first();

        return $est ? (int) $est->id : 185;
    }

    /**
     * Lista paginada de proveedores con sus documentos asociados.
     */
    public function index(Request $request)
    {
        $perPage = (int) ($request->input('per_page', 15));
        if ($perPage <= 0) {
            $perPage = 15;
        }

        $filters = $request->only(['search', 'tipo_persona', 'rfc', 'razon_social']);

        $query = Proveedores::filter($filters);

        if ($request->filled('con_docs_completos') && (int) $request->input('con_docs_completos') === 1) {
            // Filtrar proveedores que tengan los 4 documentos requeridos
            $query->whereHas('docs', function ($q) {
                $q->whereIn('nombre_doc', [
                    'Opinión positiva',
                    'Constancia de situación fiscal',
                    'Comprobante de domicilio',
                    'Carátula bancaria',
                ]);
            }, '=', 4);
        }

        $proveedores = $query->with([
            'registrador.empleado',
            'docs.estatus',
            'docs.uploader.empleado',
            'docs.validator.empleado',
        ])
            ->orderBy('razon_social', 'asc')
            ->paginate($perPage);

        return $this->respond($proveedores);
    }

    /**
     * Catálogos para el módulo de Proveedores Autorizados.
     */
    public function getforms()
    {
        $estatusDocs = Estatus::where('tipo_estatus', 'proveedores-doc')->get();

        $data = [
            'documentos_requeridos' => [
                [
                    'key' => 'opinion_positiva',
                    'nombre' => 'Opinión positiva',
                    'descripcion' => 'Opinión del cumplimiento de obligaciones fiscales (SAT 32-D)'
                ],
                [
                    'key' => 'constancia_fiscal',
                    'nombre' => 'Constancia de situación fiscal',
                    'descripcion' => 'Constancia de situación fiscal emitida por el SAT no mayor a 3 meses'
                ],
                [
                    'key' => 'comprobante_domicilio',
                    'nombre' => 'Comprobante de domicilio',
                    'descripcion' => 'Comprobante de domicilio no mayor a 3 meses (luz, agua, teléfono)'
                ],
                [
                    'key' => 'caratula_bancaria',
                    'nombre' => 'Carátula bancaria',
                    'descripcion' => 'Carátula del estado de cuenta bancario con CLABE interbancaria visible'
                ],
            ],
            'estatus_docs' => $estatusDocs,
            'tipos_persona' => ['Física', 'Moral'],
            'estatus_por_validar_id' => $this->getEstatusPorValidarId(),
        ];

        return $this->respond($data);
    }

    /**
     * Verifica en tiempo real si un RFC ya existe en proveedores.
     */
    public function checkRfc(Request $request)
    {
        $cleanRfc = strtoupper(trim(str_replace([' ', '-'], '', (string) $request->input('rfc', ''))));
        $excludeId = $request->input('exclude_id');

        if (empty($cleanRfc)) {
            return $this->respond([
                'exists' => false,
                'proveedor' => null,
            ]);
        }

        $query = Proveedores::where('rfc', $cleanRfc);
        if (!empty($excludeId)) {
            $query->where('id', '!=', (int) $excludeId);
        }

        $existing = $query->first(['id', 'razon_social', 'rfc']);

        return $this->respond([
            'exists' => (bool) $existing,
            'proveedor' => $existing,
        ]);
    }

    /**
     * Da de alta un nuevo proveedor y guarda sus documentos en proveedores_docs.
     */
    public function store(Request $request)
    {
        if ($request->has('rfc')) {
            $cleanRfc = strtoupper(trim(str_replace([' ', '-'], '', (string) $request->input('rfc'))));
            $request->merge(['rfc' => !empty($cleanRfc) ? $cleanRfc : null]);
        }

        $validated = $request->validate([
            'razon_social' => ['required', 'string', 'max:255'],
            'tipo_persona' => ['required', 'string', 'in:Física,Moral'],
            'rfc' => [
                'required',
                'string',
                'min:12',
                'max:13',
                'regex:/^[A-Z&Ññ]{3,4}[0-9]{6}[A-Z0-9]{3}$/i',
                Rule::unique('proveedores', 'rfc'),
            ],
            'domicilio' => ['nullable', 'string', 'max:500'],
            'correo' => ['nullable', 'email', 'max:255'],
            'telefono' => ['nullable', 'string', 'max:50'],
            'dias_credito' => ['nullable', 'string', 'max:50'],
            'descripcion' => ['nullable', 'string'],
            'documentos' => ['nullable', 'array'],
            'documentos.*.nombre_doc' => ['required_with:documentos', 'string'],
            'documentos.*.base64' => ['required_with:documentos', 'string'],
            'documentos.*.fecha_vencimiento' => ['nullable', 'date'],
        ], [
            'razon_social.required' => 'La razón social es obligatoria.',
            'tipo_persona.required' => 'El tipo de persona es obligatorio.',
            'tipo_persona.in' => 'El tipo de persona debe ser Física o Moral.',
            'rfc.required' => 'El RFC es obligatorio para dar de alta al proveedor.',
            'rfc.unique' => 'El RFC ingresado ya se encuentra registrado con otro proveedor.',
            'rfc.min' => 'El RFC debe tener al menos 12 caracteres (12 para Persona Moral, 13 para Física).',
            'rfc.max' => 'El RFC no debe exceder 13 caracteres.',
            'rfc.regex' => 'El formato del RFC no es válido (ejemplo: AAA010101AAA o AAAA010101AAA).',
            'correo.email' => 'El correo electrónico no tiene un formato válido.',
        ]);

        DB::beginTransaction();
        try {
            $userId = Auth::id() ?? 1;

            $proveedor = Proveedores::create([
                'razon_social' => trim($validated['razon_social']),
                'tipo_persona' => $validated['tipo_persona'],
                'rfc' => !empty($validated['rfc']) ? strtoupper(trim($validated['rfc'])) : null,
                'domicilio' => $validated['domicilio'] ?? null,
                'correo' => $validated['correo'] ?? null,
                'telefono' => $validated['telefono'] ?? null,
                'dias_credito' => $validated['dias_credito'] ?? null,
                'descripcion' => $validated['descripcion'] ?? null,
                'registered_by' => $userId,
            ]);

            $estatusId = $this->getEstatusPorValidarId();

            // Procesar documentos enviados en arreglo 'documentos'
            if (!empty($validated['documentos']) && is_array($validated['documentos'])) {
                foreach ($validated['documentos'] as $docItem) {
                    if (empty($docItem['base64']) || empty($docItem['nombre_doc'])) {
                        continue;
                    }

                    $url = $this->savePdfDocument(
                        $docItem['base64'],
                        (int) $proveedor->id,
                        $docItem['nombre_doc']
                    );

                    ProveedoresDocs::create([
                        'proveedor_id' => $proveedor->id,
                        'nombre_doc' => $docItem['nombre_doc'],
                        'url' => $url,
                        'estatus_id' => $estatusId,
                        'uploaded_by' => $userId,
                        'validated_by' => null,
                        'notas' => null,
                        'fecha_vencimiento' => $docItem['fecha_vencimiento'] ?? null,
                    ]);
                }
            }

            // También admitir archivos directos por campos nombrados
            $namedDocs = [
                'doc_opinion_positiva' => 'Opinión positiva',
                'doc_constancia_fiscal' => 'Constancia de situación fiscal',
                'doc_comprobante_domicilio' => 'Comprobante de domicilio',
                'doc_caratula_bancaria' => 'Carátula bancaria',
            ];

            foreach ($namedDocs as $inputKey => $docTitle) {
                if ($request->has($inputKey) && !empty($request->input($inputKey))) {
                    $base64Data = $request->input($inputKey);
                    $url = $this->savePdfDocument($base64Data, (int) $proveedor->id, $docTitle);

                    ProveedoresDocs::create([
                        'proveedor_id' => $proveedor->id,
                        'nombre_doc' => $docTitle,
                        'url' => $url,
                        'estatus_id' => $estatusId,
                        'uploaded_by' => $userId,
                        'validated_by' => null,
                        'notas' => null,
                    ]);
                }
            }

            DB::commit();

            $proveedor->load([
                'registrador.empleado',
                'docs.estatus',
                'docs.uploader.empleado',
                'docs.validator.empleado',
            ]);

            // Enviar notificación por correo al Coordinador de Compras y en CC al registrador (si no es Admin)
            $currentUser = Auth::user() ?? User::find($userId);
            $this->sendNuevoProveedorEmail($proveedor, $currentUser);

            return $this->respondCreated($proveedor, 'Proveedor registrado correctamente.');
        } catch (\Throwable $e) {
            DB::rollBack();
            return $this->respondError('Error al registrar el proveedor: ' . $e->getMessage(), 500);
        }
    }

    /**
     * Muestra la información de un proveedor y todo su expediente digital.
     */
    public function show($id)
    {
        $proveedor = Proveedores::with([
            'registrador.empleado',
            'docs.estatus',
            'docs.uploader.empleado',
            'docs.validator.empleado',
        ])->findOrFail($id);

        return $this->respond($proveedor);
    }

    /**
     * Actualiza la información básica del proveedor.
     */
    public function update(Request $request, $id)
    {
        $proveedor = Proveedores::findOrFail($id);

        if (!$this->canUserManageProveedor(Auth::user(), $proveedor)) {
            return $this->respondError('No tienes permisos para editar este proveedor. Solo puede ser editado por el usuario que lo registró o por usuarios con rol Proveedores.', 403);
        }

        if ($request->has('rfc')) {
            $cleanRfc = strtoupper(trim(str_replace([' ', '-'], '', (string) $request->input('rfc'))));
            $request->merge(['rfc' => !empty($cleanRfc) ? $cleanRfc : null]);
        }

        $validated = $request->validate([
            'razon_social' => ['required', 'string', 'max:255'],
            'tipo_persona' => ['required', 'string', 'in:Física,Moral'],
            'rfc' => [
                'required',
                'string',
                'min:12',
                'max:13',
                'regex:/^[A-Z&Ññ]{3,4}[0-9]{6}[A-Z0-9]{3}$/i',
                Rule::unique('proveedores', 'rfc')->ignore($id),
            ],
            'domicilio' => ['nullable', 'string', 'max:500'],
            'correo' => ['nullable', 'email', 'max:255'],
            'telefono' => ['nullable', 'string', 'max:50'],
            'dias_credito' => ['nullable', 'string', 'max:50'],
            'descripcion' => ['nullable', 'string'],
        ], [
            'razon_social.required' => 'La razón social es obligatoria.',
            'tipo_persona.required' => 'El tipo de persona es obligatorio.',
            'rfc.required' => 'El RFC es obligatorio.',
            'rfc.unique' => 'El RFC ingresado ya se encuentra registrado con otro proveedor.',
            'rfc.min' => 'El RFC debe tener al menos 12 caracteres (12 para Persona Moral, 13 para Física).',
            'rfc.max' => 'El RFC no debe exceder 13 caracteres.',
            'rfc.regex' => 'El formato del RFC no es válido (ejemplo: AAA010101AAA o AAAA010101AAA).',
            'correo.email' => 'El correo electrónico no tiene un formato válido.',
        ]);

        $proveedor->update([
            'razon_social' => trim($validated['razon_social']),
            'tipo_persona' => $validated['tipo_persona'],
            'rfc' => !empty($validated['rfc']) ? strtoupper(trim($validated['rfc'])) : null,
            'domicilio' => $validated['domicilio'] ?? null,
            'correo' => $validated['correo'] ?? null,
            'telefono' => $validated['telefono'] ?? null,
            'dias_credito' => $validated['dias_credito'] ?? null,
            'descripcion' => $validated['descripcion'] ?? null,
        ]);

        $proveedor->load([
            'registrador.empleado',
            'docs.estatus',
            'docs.uploader.empleado',
            'docs.validator.empleado',
        ]);

        return $this->respond($proveedor, 'Proveedor actualizado correctamente.');
    }

    /**
     * Sube o reemplaza un documento individual de un proveedor existente.
     */
    public function uploadDocumento(Request $request, $id)
    {
        $proveedor = Proveedores::findOrFail($id);

        if (!$this->canUserManageProveedor(Auth::user(), $proveedor)) {
            return $this->respondError('No tienes permisos para subir o actualizar documentos de este proveedor. Solo puede ser realizado por el usuario que lo registró o por usuarios con rol Proveedores.', 403);
        }

        $validated = $request->validate([
            'nombre_doc' => ['required', 'string', 'max:255'],
            'base64' => ['required', 'string'],
            'fecha_vencimiento' => ['nullable', 'date'],
            'notas' => ['nullable', 'string', 'max:500'],
        ], [
            'nombre_doc.required' => 'El tipo o nombre del documento es obligatorio.',
            'base64.required' => 'El archivo PDF es obligatorio.',
        ]);

        try {
            $url = $this->savePdfDocument(
                $validated['base64'],
                (int) $proveedor->id,
                $validated['nombre_doc']
            );

            $estatusId = $this->getEstatusPorValidarId();
            $userId = Auth::id() ?? 1;

            // Si ya existe un documento previo del mismo tipo, actualizarlo o registrar nueva versión
            $existing = ProveedoresDocs::where('proveedor_id', $proveedor->id)
                ->where('nombre_doc', $validated['nombre_doc'])
                ->first();

            if ($existing) {
                $existing->update([
                    'url' => $url,
                    'estatus_id' => $estatusId,
                    'uploaded_by' => $userId,
                    'validated_by' => null,
                    'notas' => $validated['notas'] ?? null,
                    'fecha_vencimiento' => $validated['fecha_vencimiento'] ?? $existing->fecha_vencimiento,
                ]);
                $doc = $existing;
            } else {
                $doc = ProveedoresDocs::create([
                    'proveedor_id' => $proveedor->id,
                    'nombre_doc' => $validated['nombre_doc'],
                    'url' => $url,
                    'estatus_id' => $estatusId,
                    'uploaded_by' => $userId,
                    'validated_by' => null,
                    'notas' => $validated['notas'] ?? null,
                    'fecha_vencimiento' => $validated['fecha_vencimiento'] ?? null,
                ]);
            }

            $doc->load(['estatus', 'uploader.empleado', 'validator.empleado']);

            // Enviar notificación por correo al Coordinador de Compras y en CC al usuario que subió (si no es Admin)
            $currentUser = Auth::user() ?? User::find($userId);
            $this->sendDocumentoSubidoEmail($proveedor, $doc, $currentUser, (bool) $existing);

            return $this->respondCreated($doc, 'Documento subido correctamente. Estatus asignado: Por Validar.');
        } catch (\Throwable $e) {
            return $this->respondError('Error al subir el documento: ' . $e->getMessage(), 500);
        }
    }

    /**
     * Valida o cambia el estatus de un documento ('Validado', 'Corregir', 'Por Validar').
     */
    public function changeDocStatus(Request $request, $docId)
    {
        $doc = ProveedoresDocs::findOrFail($docId);

        $user = Auth::user();
        if (!$user || (!$user->hasRole('Proveedores') && !$user->hasRole('Admin'))) {
            return $this->respondError('No tienes permisos para validar o cambiar el estatus de este documento. Solo los usuarios con rol Proveedores pueden validar documentos.', 403);
        }

        $validated = $request->validate([
            'estatus_id' => ['required', 'integer', 'exists:estatus,id'],
            'notas' => ['nullable', 'string', 'max:1000'],
        ], [
            'estatus_id.required' => 'El estatus es obligatorio.',
            'estatus_id.exists' => 'El estatus seleccionado no es válido.',
        ]);

        $doc->update([
            'estatus_id' => $validated['estatus_id'],
            'validated_by' => Auth::id() ?? null,
            'notas' => $validated['notas'] ?? $doc->notas,
        ]);

        $doc->load([
            'estatus',
            'uploader.empleado',
            'validator.empleado',
            'proveedor.registrador.empleado',
        ]);

        // Enviar notificación por correo al uploader o registrador del proveedor
        $this->sendDocumentoValidadoEmail($doc, $user);

        return $this->respond($doc, 'Estatus del documento actualizado correctamente.');
    }

    /**
     * Elimina un documento del expediente del proveedor.
     */
    public function deleteDocumento($docId)
    {
        $doc = ProveedoresDocs::findOrFail($docId);
        $proveedor = $doc->proveedor;

        if ($proveedor && !$this->canUserManageProveedor(Auth::user(), $proveedor)) {
            return $this->respondError('No tienes permisos para eliminar documentos de este proveedor.', 403);
        }

        if (!empty($doc->url)) {
            try {
                // Si la URL es una ruta relativa o contiene la ruta S3
                $path = parse_url($doc->url, PHP_URL_PATH);
                $cleanPath = ltrim($path, '/');
                if (Storage::disk('s3')->exists($cleanPath)) {
                    Storage::disk('s3')->delete($cleanPath);
                }
            } catch (\Throwable $e) {
                // Continuar aunque no se pueda borrar de S3
            }
        }

        $doc->delete();

        return $this->respondSuccess('Documento eliminado correctamente.');
    }

    /**
     * Guarda el archivo PDF en AWS S3 y retorna su URL pública accesible.
     * Valida estrictamente que sea un documento en formato PDF.
     */
    private function savePdfDocument(string $base64, int $proveedorId, string $nombreDoc): string
    {
        if (!preg_match('/^data:([a-zA-Z0-9]+\/[a-zA-Z0-9-.+]+)?;base64,/', $base64, $matches)) {
            throw new \Exception('El formato del archivo no es válido.');
        }

        $mimeType = strtolower($matches[1] ?? '');
        if ($mimeType !== 'application/pdf') {
            throw new \Exception('Solo se permiten documentos en formato PDF.');
        }

        $data = substr($base64, strpos($base64, ',') + 1);
        $decoded = base64_decode($data);
        if ($decoded === false || substr($decoded, 0, 4) !== '%PDF') {
            throw new \Exception('El archivo proporcionado no es un documento PDF válido.');
        }

        $docSlug = Str::slug($nombreDoc, '_');
        $timestamp = now()->format('Ymd_His');
        $random = Str::random(6);
        $fileName = "{$docSlug}_{$timestamp}_{$random}.pdf";

        $folder = "proveedores/proveedor_{$proveedorId}/documentos";
        $filePath = "{$folder}/{$fileName}";

        Storage::disk('s3')->put($filePath, $decoded);

        return Storage::disk('s3')->url($filePath);
    }

    /**
     * Envía correo de notificación cuando se da de alta un nuevo proveedor.
     * - Destinatario (TO): Coordinador de compras.
     * - En copia (CC): Usuario que subió el proveedor (excepto si es Admin).
     */
    protected function sendNuevoProveedorEmail(Proveedores $proveedor, ?User $user): void
    {
        try {
            // 1. Obtener correo del Coordinador de Compras
            $coordCompras = Empleado::whereHas('puesto', function ($q) {
                $q->where('nombre', 'Coordinador de compras');
            })->whereHas('estatus', function ($q) {
                $q->where('nombre', 'Activo');
            })->first();

            // $correoCoord = optional($coordCompras)->correo_institucional;
            $correoCoord = 'munozchristian@etbsa-online.com.mx';

            if (empty($correoCoord)) {
                Log::warning('No se pudo enviar notificación de nuevo proveedor: correo de Coordinador de compras no encontrado.', [
                    'proveedor_id' => $proveedor->id,
                ]);
                return;
            }

            $mail = Mail::to($correoCoord);

            // 2. Verificar si el usuario registrador es Admin
            $isAdmin = false;
            if ($user) {
                if (method_exists($user, 'hasRole') && $user->hasRole('Admin')) {
                    $isAdmin = true;
                } elseif (!empty($user->role) && strtolower($user->role) === 'admin') {
                    $isAdmin = true;
                }
            }

            // 3. En copia al que subió el proveedor (a menos que sea Admin)
            if (!$isAdmin && $user) {
                $correoRegistrador = $user->email ?? optional($user->empleado)->correo_institucional;
                if (!empty($correoRegistrador) && strtolower($correoRegistrador) !== strtolower($correoCoord)) {
                    $mail->cc($correoRegistrador);
                }
            }

            $mail->send(new NuevoProveedorMail($proveedor, $user));
        } catch (\Throwable $e) {
            Log::error('Error al enviar correo de nuevo proveedor: ' . $e->getMessage(), [
                'proveedor_id' => $proveedor->id,
                'exception' => $e,
            ]);
        }
    }

    /**
     * Envía correo de notificación cuando se sube o actualiza un documento individual de un proveedor.
     * - Destinatario (TO): Coordinador de compras.
     * - En copia (CC): Usuario que subió el documento (excepto si es Admin).
     */
    protected function sendDocumentoSubidoEmail(
        Proveedores $proveedor,
        ProveedoresDocs $documento,
        ?User $user,
        bool $esReemplazo = false
    ): void {
        try {
            // 1. Obtener correo del Coordinador de Compras
            $coordCompras = Empleado::whereHas('puesto', function ($q) {
                $q->where('nombre', 'Coordinador de compras');
            })->whereHas('estatus', function ($q) {
                $q->where('nombre', 'Activo');
            })->first();

            // $correoCoord = optional($coordCompras)->correo_institucional;
            $correoCoord = 'munozchristian@etbsa-online.com.mx';

            if (empty($correoCoord)) {
                Log::warning('No se pudo enviar notificación de documento subido: correo de Coordinador de compras no encontrado.', [
                    'proveedor_id' => $proveedor->id,
                    'documento_id' => $documento->id,
                ]);
                return;
            }

            $mail = Mail::to($correoCoord);

            // 2. Verificar si el usuario que sube el documento es Admin
            $isAdmin = false;
            if ($user) {
                if (method_exists($user, 'hasRole') && $user->hasRole('Admin')) {
                    $isAdmin = true;
                } elseif (!empty($user->role) && strtolower($user->role) === 'admin') {
                    $isAdmin = true;
                }
            }

            // 3. En copia al usuario que subió el documento (a menos que sea Admin)
            if (!$isAdmin && $user) {
                $correoUploader = $user->email ?? optional($user->empleado)->correo_institucional;
                if (!empty($correoUploader) && strtolower($correoUploader) !== strtolower($correoCoord)) {
                    $mail->cc($correoUploader);
                }
            }

            $mail->send(new DocumentoProveedorSubidoMail($proveedor, $documento, $user, $esReemplazo));
        } catch (\Throwable $e) {
            Log::error('Error al enviar correo de documento subido: ' . $e->getMessage(), [
                'proveedor_id' => $proveedor->id,
                'documento_id' => $documento->id,
                'exception' => $e,
            ]);
        }
    }

    /**
     * Envía correo de notificación cuando se valida un documento o se marca a corregir.
     * - Destinatario principal: Usuario que subió el documento (uploader).
     * - Respaldo / En copia: Usuario que dio de alta al proveedor (registrador).
     */
    protected function sendDocumentoValidadoEmail(ProveedoresDocs $doc, ?User $validator): void
    {
        try {
            $estatusNombre = strtolower($doc->estatus?->nombre ?? '');
            if (!in_array($estatusNombre, ['validado', 'corregir'])) {
                return;
            }

            $uploader = $doc->uploader;
            $registrador = $doc->proveedor?->registrador;

            $correoUploader = $uploader?->email ?? optional($uploader?->empleado)->correo_institucional;
            $correoRegistrador = $registrador?->email ?? optional($registrador?->empleado)->correo_institucional;

            $destinatario = $correoUploader ?: $correoRegistrador;

            if (empty($destinatario)) {
                Log::warning('No se pudo enviar notificación de validación de documento: no se encontró correo del uploader ni del registrador.', [
                    'doc_id' => $doc->id,
                ]);
                return;
            }

            $mail = Mail::to($destinatario);

            if (!empty($correoUploader) && !empty($correoRegistrador) && strtolower($correoRegistrador) !== strtolower($correoUploader)) {
                $mail->cc($correoRegistrador);
            }

            $mail->send(new DocumentoValidadoMail($doc, $validator));
        } catch (\Throwable $e) {
            Log::error('Error al enviar correo de validación de documento: ' . $e->getMessage(), [
                'doc_id' => $doc->id,
                'exception' => $e,
            ]);
        }
    }
}
