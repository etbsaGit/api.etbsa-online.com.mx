<?php

namespace App\Http\Controllers\Api;

use App\Models\Vehicle;
use Illuminate\Support\Str;
use Illuminate\Http\Request;
use App\Models\VehiclesDocumentos;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use App\Http\Controllers\ApiController;
use App\Http\Requests\Vehicle\StoreVehicleDocumentoRequest;
use App\Http\Requests\Vehicle\UpdateVehicleDocumentoRequest;

class VehicleDocumentoController extends ApiController
{
    private const REGISTRADOR_RELATIONS = [
        'registrador:id,name,email',
        'registrador.empleado:id,user_id,nombre,segundo_nombre,apellido_paterno,apellido_materno',
    ];

    private function optimizeDocumentoRegistrador($items): void
    {
        $collection = $items instanceof \Illuminate\Support\Collection ? $items : collect([$items]);
        $empAppends = ['nombreCompleto', 'apellidoCompleto'];

        foreach ($collection as $doc) {
            if (!$doc) {
                continue;
            }
            if ($doc->relationLoaded('registrador') && $doc->registrador) {
                if ($doc->registrador->relationLoaded('empleado') && $doc->registrador->empleado) {
                    $doc->registrador->empleado->setAppends($empAppends);
                }
            }
        }
    }

    /**
     * Lista todos los documentos del expediente de un vehículo
     * (tanto los vigentes por categoría como el historial completo).
     */
    public function index(Request $request, Vehicle $vehicle)
    {
        $documentos = VehiclesDocumentos::where('vehicle_id', $vehicle->id)
            ->with(self::REGISTRADOR_RELATIONS)
            ->orderBy('es_vigente', 'desc')
            ->orderBy('fecha_emision', 'desc')
            ->orderBy('id', 'desc')
            ->get();

        $this->optimizeDocumentoRegistrador($documentos);

        $vigentes = [
            'seguro' => $documentos->first(fn ($d) => $d->tipo_documento === 'seguro' && $d->es_vigente),
            'tarjeta_circulacion' => $documentos->first(fn ($d) => $d->tipo_documento === 'tarjeta_circulacion' && $d->es_vigente),
            'verificacion' => $documentos->first(fn ($d) => $d->tipo_documento === 'verificacion' && $d->es_vigente),
        ];

        return $this->respond([
            'vehicle_id' => $vehicle->id,
            'vigentes' => $vigentes,
            'documentos' => $documentos,
        ]);
    }

    /**
     * Registra un nuevo documento (o renovación) en el expediente del vehículo.
     * Si se marca como es_vigente = true (por defecto), los documentos anteriores
     * del mismo tipo_documento para ese vehículo pasan automáticamente a es_vigente = false (historial).
     */
    public function store(StoreVehicleDocumentoRequest $request)
    {
        $validated = $request->validated();

        DB::beginTransaction();
        try {
            $vehicleId = (int) $validated['vehicle_id'];
            $tipoDoc = $validated['tipo_documento'];
            $esVigente = array_key_exists('es_vigente', $validated)
                ? (bool) $validated['es_vigente']
                : true;

            if ($esVigente) {
                VehiclesDocumentos::where('vehicle_id', $vehicleId)
                    ->where('tipo_documento', $tipoDoc)
                    ->where('es_vigente', true)
                    ->update(['es_vigente' => false]);
            }

            $archivoPath = null;
            if (!empty($validated['base64'])) {
                $archivoPath = $this->saveBase64File($validated['base64'], $vehicleId, $tipoDoc);
            } elseif ($request->hasFile('archivo')) {
                $archivoPath = $this->saveUploadedFile($request->file('archivo'), $vehicleId, $tipoDoc);
            }

            $documento = VehiclesDocumentos::create([
                'vehicle_id' => $vehicleId,
                'tipo_documento' => $tipoDoc,
                'folio_o_poliza' => !empty($validated['folio_o_poliza']) ? trim($validated['folio_o_poliza']) : null,
                'detalle_extra' => !empty($validated['detalle_extra']) ? trim($validated['detalle_extra']) : null,
                'fecha_emision' => $validated['fecha_emision'],
                'fecha_vencimiento' => $validated['fecha_vencimiento'] ?? null,
                'archivo_path' => $archivoPath,
                'es_vigente' => $esVigente,
                'observaciones' => !empty($validated['observaciones']) ? trim($validated['observaciones']) : null,
                'registered_by' => Auth::id(),
            ]);

            DB::commit();

            $documento->load(self::REGISTRADOR_RELATIONS);
            $this->optimizeDocumentoRegistrador($documento);

            return $this->respondCreated($documento, 'Documento guardado correctamente en el expediente.');
        } catch (\Throwable $e) {
            DB::rollBack();
            return $this->respondError('Error al guardar el documento: ' . $e->getMessage(), 500);
        }
    }

    /**
     * Muestra el detalle de un documento específico.
     */
    public function show(VehiclesDocumentos $vehicleDocumento)
    {
        $vehicleDocumento->load(array_merge(['vehicle'], self::REGISTRADOR_RELATIONS));
        $this->optimizeDocumentoRegistrador($vehicleDocumento);
        return $this->respond($vehicleDocumento);
    }

    /**
     * Actualiza un documento existente del expediente del vehículo.
     */
    public function update(UpdateVehicleDocumentoRequest $request, VehiclesDocumentos $vehicleDocumento)
    {
        $validated = $request->validated();

        DB::beginTransaction();
        try {
            $vehicleId = (int) ($validated['vehicle_id'] ?? $vehicleDocumento->vehicle_id);
            $tipoDoc = $validated['tipo_documento'];
            $esVigente = array_key_exists('es_vigente', $validated)
                ? (bool) $validated['es_vigente']
                : (bool) $vehicleDocumento->es_vigente;

            if ($esVigente) {
                VehiclesDocumentos::where('vehicle_id', $vehicleId)
                    ->where('tipo_documento', $tipoDoc)
                    ->where('id', '!=', $vehicleDocumento->id)
                    ->where('es_vigente', true)
                    ->update(['es_vigente' => false]);
            }

            $archivoPath = $vehicleDocumento->archivo_path;

            if ($request->boolean('eliminar_archivo') && !empty($archivoPath)) {
                $this->deleteFileFromS3($archivoPath);
                $archivoPath = null;
            }

            if (!empty($validated['base64'])) {
                if (!empty($archivoPath)) {
                    $this->deleteFileFromS3($archivoPath);
                }
                $archivoPath = $this->saveBase64File($validated['base64'], $vehicleId, $tipoDoc);
            } elseif ($request->hasFile('archivo')) {
                if (!empty($archivoPath)) {
                    $this->deleteFileFromS3($archivoPath);
                }
                $archivoPath = $this->saveUploadedFile($request->file('archivo'), $vehicleId, $tipoDoc);
            }

            $vehicleDocumento->update([
                'vehicle_id' => $vehicleId,
                'tipo_documento' => $tipoDoc,
                'folio_o_poliza' => !empty($validated['folio_o_poliza']) ? trim($validated['folio_o_poliza']) : null,
                'detalle_extra' => !empty($validated['detalle_extra']) ? trim($validated['detalle_extra']) : null,
                'fecha_emision' => $validated['fecha_emision'],
                'fecha_vencimiento' => $validated['fecha_vencimiento'] ?? null,
                'archivo_path' => $archivoPath,
                'es_vigente' => $esVigente,
                'observaciones' => !empty($validated['observaciones']) ? trim($validated['observaciones']) : null,
            ]);

            DB::commit();

            $vehicleDocumento->load(self::REGISTRADOR_RELATIONS);
            $this->optimizeDocumentoRegistrador($vehicleDocumento);

            return $this->respond($vehicleDocumento, 'Documento actualizado correctamente.');
        } catch (\Throwable $e) {
            DB::rollBack();
            return $this->respondError('Error al actualizar el documento: ' . $e->getMessage(), 500);
        }
    }

    /**
     * Marca un documento específico del historial como el documento vigente de su categoría.
     */
    public function marcarVigente(VehiclesDocumentos $vehicleDocumento)
    {
        DB::beginTransaction();
        try {
            VehiclesDocumentos::where('vehicle_id', $vehicleDocumento->vehicle_id)
                ->where('tipo_documento', $vehicleDocumento->tipo_documento)
                ->where('id', '!=', $vehicleDocumento->id)
                ->update(['es_vigente' => false]);

            $vehicleDocumento->update(['es_vigente' => true]);

            DB::commit();

            $vehicleDocumento->load(self::REGISTRADOR_RELATIONS);
            $this->optimizeDocumentoRegistrador($vehicleDocumento);

            return $this->respond($vehicleDocumento, 'Documento establecido como vigente.');
        } catch (\Throwable $e) {
            DB::rollBack();
            return $this->respondError('Error al marcar el documento como vigente: ' . $e->getMessage(), 500);
        }
    }

    /**
     * Elimina un documento del expediente. Si era el vigente, promueve el más reciente del historial.
     */
    public function destroy(VehiclesDocumentos $vehicleDocumento)
    {
        DB::beginTransaction();
        try {
            $vehicleId = $vehicleDocumento->vehicle_id;
            $tipoDoc = $vehicleDocumento->tipo_documento;
            $eraVigente = (bool) $vehicleDocumento->es_vigente;

            if (!empty($vehicleDocumento->archivo_path)) {
                $this->deleteFileFromS3($vehicleDocumento->archivo_path);
            }

            $vehicleDocumento->delete();

            if ($eraVigente) {
                $ultimoHistorico = VehiclesDocumentos::where('vehicle_id', $vehicleId)
                    ->where('tipo_documento', $tipoDoc)
                    ->orderBy('fecha_emision', 'desc')
                    ->orderBy('id', 'desc')
                    ->first();

                if ($ultimoHistorico) {
                    $ultimoHistorico->update(['es_vigente' => true]);
                }
            }

            DB::commit();

            return $this->respondSuccess('Documento eliminado correctamente.');
        } catch (\Throwable $e) {
            DB::rollBack();
            return $this->respondError('Error al eliminar el documento: ' . $e->getMessage(), 500);
        }
    }

    /**
     * Guarda un archivo PDF recibido en formato Base64 en S3.
     */
    private function saveBase64File(string $base64, int $vehicleId, string $tipoDoc): string
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

        $slug = Str::slug($tipoDoc, '_');
        $timestamp = now()->format('Ymd_His');
        $random = Str::random(6);
        $fileName = "{$slug}_{$timestamp}_{$random}.pdf";
        $filePath = "vehicles/vehicle_{$vehicleId}/expediente/{$fileName}";

        Storage::disk('s3')->put($filePath, $decoded);

        return Storage::disk('s3')->url($filePath);
    }

    /**
     * Guarda un archivo PDF subido por multipart/form-data en S3.
     */
    private function saveUploadedFile($file, int $vehicleId, string $tipoDoc): string
    {
        $ext = strtolower($file->getClientOriginalExtension() ?: '');
        $mime = strtolower($file->getMimeType() ?: '');
        if ($ext !== 'pdf' && $mime !== 'application/pdf') {
            throw new \Exception('Solo se permiten documentos en formato PDF.');
        }

        $slug = Str::slug($tipoDoc, '_');
        $timestamp = now()->format('Ymd_His');
        $random = Str::random(6);
        $fileName = "{$slug}_{$timestamp}_{$random}.pdf";
        $folder = "vehicles/vehicle_{$vehicleId}/expediente";

        $path = Storage::disk('s3')->putFileAs($folder, $file, $fileName);

        return Storage::disk('s3')->url($path);
    }

    /**
     * Elimina un archivo de S3 a partir de su URL o path relativo.
     */
    private function deleteFileFromS3(?string $urlOrPath): void
    {
        if (empty($urlOrPath)) {
            return;
        }

        try {
            $path = str_starts_with($urlOrPath, 'http://') || str_starts_with($urlOrPath, 'https://')
                ? ltrim((string) parse_url($urlOrPath, PHP_URL_PATH), '/')
                : ltrim($urlOrPath, '/');

            if (!empty($path) && Storage::disk('s3')->exists($path)) {
                Storage::disk('s3')->delete($path);
            }
        } catch (\Throwable $e) {
            // Continuar aunque falle el borrado físico en S3
        }
    }
}
