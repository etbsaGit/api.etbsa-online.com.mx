<?php

namespace App\Http\Controllers\Intranet;

use Illuminate\Http\Request;
use App\Http\Controllers\ApiController;
use App\Http\Requests\Intranet\CreditoInterno\AutorizarCreditoInternoRequest;
use App\Http\Requests\Intranet\CreditoInterno\CreditoInternoRequest;
use App\Http\Requests\Intranet\Products\TractorContrapesoRequest;
use App\Models\Empleado;
use App\Models\Estatus;
use App\Models\Intranet\Cliente;
use App\Models\Intranet\ClientesDoc;
use App\Models\Intranet\CreditoInterno\CreditoDocs;
use App\Models\Intranet\CreditoInterno\CreditoDocsRequeridos;
use App\Models\Intranet\CreditoInterno\CreditoDocsSolicitados;
use App\Models\Intranet\CreditoInterno\CreditoHistorialPagos;
use App\Models\Intranet\CreditoInterno\CreditoHistorical;
use App\Models\Intranet\CreditoInterno\CreditoLineas;
use App\Models\Intranet\CreditoInterno\CreditoSolicitud;
use App\Models\Puesto;
use App\Models\Sucursal;
use App\Traits\UploadableFile;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class CreditoInternoController extends ApiController
{
    use UploadableFile;
    public function index(Request $request)
    {
        $filters = $request->all();
        $user = Auth::user();
        // si es admin o director administrativo puede ver todas las cotizaciones, si no sólo las que se le notificó al usuario
        $creditoSolicitudes = CreditoSolicitud::query()
            ->when(!$user->hasRole('Admin') && !$user->hasRole('Credito') && $user->empleado->puesto_id !== Puesto::where('nombre', 'Director Administrativo')->first()->id, function ($query) use ($user) {
                $query->whereHas('notificado', function ($q) use ($user) {
                    $q->where('id', $user->empleado->id);
                });
            })->with([
                'cliente',
                'asesor',
                'notificado',
                'estatus',
                'validadoPor',
                'tipoEnganche',
                'linea.tiposEnganche',
                'pagos.estatus',
                'historial.estatus',
                'historial.empleado',
                'sucursal',
                'pagos.actualizadoPor',
                'pagos.validadoPor',
                'pagos.documento.documento',
                'documentacion.documento'
            ])->filter($filters)->orderBy('created_at', 'desc')->paginate(10);
        return $this->respond(
            $creditoSolicitudes,
            'Lista de solicitudes de crédito cargada correctamente'
        );
    }

    public function store(CreditoInternoRequest $request)
    {
        DB::beginTransaction();

        try {
            $data = $request->validated();

            // crear folio
            $data['folio'] = str_pad(
                CreditoSolicitud::max('id') + 1,
                6,
                '0',
                STR_PAD_LEFT
            );

            $estatusId = Estatus::where('nombre', 'Crédito Solicitado')->where('tipo_estatus', 'credito-interno')->first();

            $data['estatus_id'] = $estatusId->id;

            $creditoSolicitud = CreditoSolicitud::create($data);

            $estatusPagoPendiente = Estatus::where('nombre', 'Pago Pendiente')->where('tipo_estatus', 'credito-interno')->first();

            // Guardar cada pago en la tabla credito_historial_pagos
            if (!empty($request->pagos)) {
                foreach ($request->pagos as $index => $pago) {
                    $esPrimerPago = ($index === 0) || (($pago['numero'] ?? null) == 1);

                    CreditoHistorialPagos::create([
                        'solicitud_id'    => $creditoSolicitud->id,
                        'n_pago'          => $pago['numero'] ?? ($index + 1),
                        'saldo_pendiente' => $esPrimerPago ? $data['monto_solicitado'] : null,
                        'etiqueta'        => $pago['etiqueta'] ?? null,
                        'fecha_a_pagar'   => $pago['fecha'],
                        'estatus_id'      => $estatusPagoPendiente->id,
                    ]);
                }
            }

            if (!empty($request->archivos)) {
                $this->guardarArchivosSolicitud($creditoSolicitud, $request->archivos);
            }
            $user = Auth::user();
            $empleadoId = $user->empleado?->id;
            CreditoHistorical::create([
                'solicitud_id' => $creditoSolicitud->id,
                'estatus_id' => $estatusId->id,
                'descripcion' => "Solicitud de crédito creada. Monto: " . ($request->monto_solicitado ?? 'N/A') . ' Motivo: ' . ($request->motivo ?? 'N/A') . ' Notas adicionales: ' . ($request->notas ?? 'N/A'),
                'empleado_id' => $empleadoId
            ]);

            DB::commit();

            return $this->respondCreated(
                $creditoSolicitud,
                'Solicitud de crédito creada'
            );
        } catch (\Throwable $e) {
            DB::rollBack();

            return response()->json([
                'message' => $e->getMessage()
            ], 500);
        }
    }

    public function registrarPago(Request $request, int $pagoId)
    {
        DB::beginTransaction();

        try {
            $pago = CreditoHistorialPagos::find($pagoId);

            if (!$pago) {
                return $this->respondNotFound('Pago no encontrado');
            }

            $solicitud = CreditoSolicitud::find($pago->solicitud_id);
            if (!$solicitud) {
                return $this->respondNotFound('Solicitud de crédito no encontrada');
            }

            $user = Auth::user();
            $empleadoId = $user->empleado?->id;

            $estatusPagoRealizado = Estatus::where('nombre', 'Pago Realizado')->where('tipo_estatus', 'credito-interno')->first();
            $estatusPagoPendiente = Estatus::where('nombre', 'Pago Pendiente')->where('tipo_estatus', 'credito-interno')->first();
            $estatusCreditoPagado = Estatus::where('nombre', 'Crédito Pagado')->where('tipo_estatus', 'credito-interno')->first();
            $estatusCreditoEnProceso = Estatus::where('nombre', 'Crédito en Proceso')->where('tipo_estatus', 'credito-interno')->first();
            $estatusCreditoAprobado = Estatus::where('nombre', 'Crédito Aprobado')->where('tipo_estatus', 'credito-interno')->first();

            $todosPagos = CreditoHistorialPagos::where('solicitud_id', $solicitud->id)
                ->orderBy('n_pago', 'asc')
                ->get();

            $montoPagado = $request->input('monto_pagado');

            // 1. Validar que no se intente pagar si los pagos anteriores no se han cubierto
            $pagoIndex = $todosPagos->search(fn($p) => $p->id === $pago->id);
            if ($pagoIndex > 0) {
                $pagoPrevio = $todosPagos[$pagoIndex - 1];
                if ($pagoPrevio->monto_pagado === null || (float)$pagoPrevio->monto_pagado <= 0) {
                    return response()->json([
                        'message' => 'Debe registrar primero los pagos anteriores pendientes.'
                    ], 422);
                }
            }

            // 2. Calcular el saldo pendiente disponible para este pago antes de aplicar el nuevo monto
            $totalPagadoAnteriores = 0;
            for ($i = 0; $i < $pagoIndex; $i++) {
                $totalPagadoAnteriores += (float)($todosPagos[$i]->monto_pagado ?? 0);
            }
            $saldoDisponibleEstePago = max(0, (float)$solicitud->monto_solicitado - $totalPagadoAnteriores);

            // 3. Validar sobrepago
            if ($montoPagado !== null && is_numeric($montoPagado)) {
                $montoFloat = (float)$montoPagado;
                if ($montoFloat < 0) {
                    return response()->json([
                        'message' => 'El monto a pagar no puede ser negativo.'
                    ], 422);
                }
                if ($montoFloat > $saldoDisponibleEstePago) {
                    return response()->json([
                        'message' => "El monto a pagar ($" . number_format($montoFloat, 2) . ") no puede ser mayor al saldo pendiente disponible ($" . number_format($saldoDisponibleEstePago, 2) . ")."
                    ], 422);
                }
            }

            // 4. Validar y procesar archivo de evidencia en PDF
            $archivoInput = $request->input('archivo');
            $base64 = is_array($archivoInput) ? ($archivoInput['base64'] ?? null) : $request->input('base64');

            if ($montoPagado !== null && is_numeric($montoPagado) && (float)$montoPagado > 0) {
                if (!$pago->document_id && empty($base64)) {
                    return response()->json([
                        'message' => 'El comprobante o evidencia de pago en formato PDF es obligatorio.'
                    ], 422);
                }
            }

            if (!empty($base64)) {
                $esEnganche = ($pago->etiqueta && stripos($pago->etiqueta, 'enganche') !== false) ||
                    ($pago->n_pago === 1 && (float)($solicitud->valor_enganche ?? $solicitud->anticipo ?? 0) > 0);

                $nombreTipoDoc = $esEnganche ? 'Evidencia enganche' : 'Evidencia pago';
                $docSolicitado = CreditoDocsSolicitados::where('nombre', $nombreTipoDoc)->first();
                if (!$docSolicitado) {
                    $docSolicitado = CreditoDocsSolicitados::create(['nombre' => $nombreTipoDoc]);
                }

                $folder = "intranet/credito_interno/folio_" . ($solicitud->folio ?? $solicitud->id);
                $relativePath = $this->saveDoc($base64, $folder);
                $ext = is_array($archivoInput) ? ($archivoInput['extension'] ?? 'pdf') : 'pdf';

                $creditoDoc = CreditoDocs::create([
                    'solicitud_id' => $solicitud->id,
                    'documento_id' => $docSolicitado->id,
                    'path'         => $relativePath,
                    'extension'    => $ext ?: 'pdf',
                    'uploaded_by'  => $empleadoId,
                ]);

                $pago->document_id = $creditoDoc->id;

                CreditoHistorical::create([
                    'solicitud_id' => $solicitud->id,
                    'estatus_id'   => Estatus::where('nombre', 'Documento Adjuntado')->where('tipo_estatus', 'credito-interno')->first()?->id ?? $solicitud->estatus_id,
                    'descripcion'  => "Documento ($nombreTipoDoc) adjuntado como evidencia para " . ($pago->etiqueta ?: "Pago #{$pago->n_pago}") . ".",
                    'empleado_id'  => $empleadoId
                ]);
            }

            // 5. Actualizar el pago actual
            if ($montoPagado !== null && is_numeric($montoPagado) && (float)$montoPagado > 0) {
                $pago->monto_pagado = (float)$montoPagado;
                $pago->fecha_liquidado = $request->input('fecha_liquidado') ?? Carbon::now()->format('Y-m-d');
                $pago->estatus_id = $estatusPagoRealizado?->id ?? $pago->estatus_id;
                $pago->updated_by = $empleadoId;
            } else {
                $pago->monto_pagado = null;
                $pago->fecha_liquidado = null;
                $pago->estatus_id = $estatusPagoPendiente?->id ?? $pago->estatus_id;
                $pago->updated_by = $empleadoId;
            }
            $pago->save();

            // 6. Recalcular saldo y estatus en cascada para TODOS los pagos
            $todosPagos = CreditoHistorialPagos::where('solicitud_id', $solicitud->id)
                ->orderBy('n_pago', 'asc')
                ->get();

            $saldoActual = (float)$solicitud->monto_solicitado;
            $liquidadoAnticipadamente = false;
            $fechaLiquidacion = $pago->fecha_liquidado ?? Carbon::now()->format('Y-m-d');

            foreach ($todosPagos as $idx => $p) {
                if ($idx === 0) {
                    $p->saldo_pendiente = (float)$solicitud->monto_solicitado;
                } else {
                    if ($liquidadoAnticipadamente) {
                        // Si ya se liquidó la deuda total en un pago anterior, este pago queda en saldo 0 y marcado como realizado
                        $p->saldo_pendiente = 0.00;
                        $p->monto_pagado = $p->monto_pagado !== null && (float)$p->monto_pagado > 0 ? $p->monto_pagado : 0.00;
                        $p->fecha_liquidado = $p->fecha_liquidado ?? $fechaLiquidacion;
                        $p->estatus_id = $estatusPagoRealizado?->id ?? $p->estatus_id;
                        $p->updated_by = $p->updated_by ?? $empleadoId;
                    } else {
                        $pagoAnterior = $todosPagos[$idx - 1];
                        if ($pagoAnterior->monto_pagado !== null && (float)$pagoAnterior->monto_pagado > 0) {
                            $p->saldo_pendiente = max(0, $saldoActual);
                        } else {
                            $p->saldo_pendiente = null;
                            if ($p->id !== $pago->id) {
                                // Limpiar estado si se desmarcó el pago anterior
                                $p->monto_pagado = null;
                                $p->fecha_liquidado = null;
                                $p->estatus_id = $estatusPagoPendiente?->id ?? $p->estatus_id;
                                $p->updated_by = $empleadoId;
                            }
                        }
                    }
                }

                // Descontar abono de este pago si tiene
                if ($p->monto_pagado !== null && (float)$p->monto_pagado > 0) {
                    $saldoActual = max(0, $saldoActual - (float)$p->monto_pagado);
                }

                // Si al descontar este pago el saldo restante total llega a 0
                if ($saldoActual <= 0 && !$liquidadoAnticipadamente) {
                    $liquidadoAnticipadamente = true;
                    if ($p->monto_pagado !== null && (float)$p->monto_pagado > 0) {
                        $p->estatus_id = $estatusPagoRealizado?->id ?? $p->estatus_id;
                        $p->fecha_liquidado = $p->fecha_liquidado ?? $fechaLiquidacion;
                        $p->updated_by = $p->updated_by ?? $empleadoId;
                    }
                }

                $p->save();
            }

            // 7. Actualizar estatus general de la solicitud de crédito
            if ($saldoActual <= 0 && $estatusCreditoPagado) {
                $solicitud->update(['estatus_id' => $estatusCreditoPagado->id]);
            } else {
                // Si aún hay saldo pendiente y la solicitud estaba marcada como Pagada, revertir
                if ($solicitud->estatus_id === $estatusCreditoPagado?->id) {
                    $nuevoEstatus = $estatusCreditoEnProceso ?? $estatusCreditoAprobado;
                    if ($nuevoEstatus) {
                        $solicitud->update(['estatus_id' => $nuevoEstatus->id]);
                    }
                }
            }

            // 8. Registrar en bitácora de historial
            $estatusHistorial = Estatus::where('nombre', 'Pago Realizado')->where('tipo_estatus', 'credito-interno')->first();
            CreditoHistorical::create([
                'solicitud_id' => $solicitud->id,
                'estatus_id'   => $estatusHistorial?->id ?? $solicitud->estatus_id,
                'descripcion'  => "Pago (" . ($pago->etiqueta ?: "Pago #{$pago->n_pago}") . ") registrado: Monto pagado: " . ($pago->monto_pagado !== null ? '$' . number_format($pago->monto_pagado, 2) : '$0.00') . ". Saldo pendiente actual: $" . number_format($saldoActual, 2),
                'empleado_id'  => $empleadoId
            ]);

            DB::commit();

            return $this->respond(
                $pago->load(['actualizadoPor', 'validadoPor', 'estatus', 'documento.documento']),
                'Pago actualizado correctamente'
            );
        } catch (\Throwable $e) {
            DB::rollBack();

            return response()->json([
                'message' => $e->getMessage()
            ], 500);
        }
    }

    public function validarPago(Request $request, int $pagoId)
    {
        DB::beginTransaction();

        try {
            $user = Auth::user();

            if (!$user->hasRole('Credito') && !$user->hasRole('Admin')) {
                return response()->json([
                    'message' => 'No tiene permisos para validar pagos. Se requiere rol de Crédito.'
                ], 403);
            }

            $pago = CreditoHistorialPagos::find($pagoId);
            if (!$pago) {
                return $this->respondNotFound('Pago no encontrado');
            }

            $solicitud = CreditoSolicitud::find($pago->solicitud_id);
            if (!$solicitud) {
                return $this->respondNotFound('Solicitud de crédito no encontrada');
            }

            if ($pago->monto_pagado === null && !$pago->fecha_liquidado) {
                return response()->json([
                    'message' => 'No se puede validar un pago que aún no ha sido registrado o liquidado.'
                ], 422);
            }

            $empleadoId = $user->empleado?->id;
            $pago->validated_by = $empleadoId;
            $pago->save();

            // Registrar en bitácora de historial
            CreditoHistorical::create([
                'solicitud_id' => $solicitud->id,
                'estatus_id'   => $pago->estatus_id ?? $solicitud->estatus_id,
                'descripcion'  => "Pago (" . ($pago->etiqueta ?: "Pago #{$pago->n_pago}") . ") de $" . number_format((float)($pago->monto_pagado ?? 0), 2) . " validado por el departamento de Crédito.",
                'empleado_id'  => $empleadoId
            ]);

            DB::commit();

            return $this->respond(
                $pago->load(['actualizadoPor', 'validadoPor', 'estatus', 'documento.documento']),
                'Pago validado correctamente'
            );
        } catch (\Throwable $e) {
            DB::rollBack();

            return response()->json([
                'message' => $e->getMessage()
            ], 500);
        }
    }

    public function update(CreditoSolicitud $credito_solicitud, CreditoInternoRequest $request)
    {
        DB::beginTransaction();

        try {
            $data = $request->validated();
            $credito_solicitud->update($data);
            DB::commit();

            return $this->respond($credito_solicitud, 'Solicitud de crédito actualizada correctamente');
        } catch (\Throwable $e) {
            DB::rollBack();

            return response()->json([
                'message' => $e->getMessage()
            ], 500);
        }
    }

    public function autorizarCredito(AutorizarCreditoInternoRequest $request, int $solicitudId, bool $autorizado)
    {
        DB::beginTransaction();

        try {
            $credito_solicitud = CreditoSolicitud::find($solicitudId);

            if ($credito_solicitud == null) {
                return $this->respondNotFound('Solicitud de crédito no encontrada');
            }

            $user = Auth::user();
            $empleadoId = $user->empleado?->id;
            $aprobadoId = Estatus::where('nombre', 'Crédito Aprobado')->where('tipo_estatus', 'credito-interno')->first();
            $data = $request->validated();
            $data['validated_by'] = $empleadoId;
            if ($autorizado) {
                $data['monto_aprobado'] = $data['monto_aprobado'];
                $data['autorizado'] = true;
                $data['estatus_id'] = $aprobadoId->id;
                $credito_solicitud->update($data);
            } else {
                $rechazadoId = Estatus::where('nombre', 'Crédito Rechazado')->where('tipo_estatus', 'credito-interno')->first();
                $data['estatus_id'] = $rechazadoId->id;
                $credito_solicitud->update($data);
            }
            DB::commit();

            return $this->respond($credito_solicitud, 'Solicitud de crédito ' . ($autorizado ? 'autorizada' : 'rechazada') . ' correctamente');
        } catch (\Throwable $e) {
            DB::rollBack();

            return response()->json([
                'message' => $e->getMessage()
            ], 500);
        }
    }

    public function destroy()
    {
        DB::beginTransaction();
        try {
        } catch (\Throwable $e) {
            DB::rollBack();
        }
    }

    public function getOptions()
    {
        $estatuses = ['Crédito Solicitado', 'Crédito Aprobado', 'Crédito Rechazado', 'Crédito en Proceso', 'Crédito Pagado'];
        $data = [
            'creditoLineas' => CreditoLineas::with('tiposEnganche')->get(),
            'gerentes' => Empleado::where('puesto_id', Puesto::where('nombre', 'Gerente Territorial')->first()->id)->with('sucursal')->where('estatus_id', Estatus::where('nombre', 'Activo')->where('tipo_estatus', 'empleado')->first()->id)->get(),
            'estatuses' => Estatus::whereIn('nombre', $estatuses)->where('tipo_estatus', 'credito-interno')->get(),
            'sucursales' => Sucursal::all(),
            'empleados' => Empleado::where('estatus_id', Estatus::where('nombre', 'Activo')->first()->id)->get(),
            'documentos' => CreditoDocsSolicitados::all(),
            'docsObligatorios' => CreditoDocsRequeridos::all()
        ];

        return $this->respond($data, 'Opciones cargadas correctamente');
    }

    public function guardarArchivosSolicitud(CreditoSolicitud $solicitud, array $archivos)
    {
        $user = Auth::user();
        $empleadoId = $user->empleado?->id;
        $folder = "intranet/credito_interno/folio_" . ($solicitud->folio ?? $solicitud->id);
        $cliente = $solicitud->cliente ?? Cliente::find($solicitud->cliente_id);
        $guardados = [];

        foreach ($archivos as $archivo) {
            $nombreArchivo = $archivo['tipo'] ?? 'Documento';
            $extension = $archivo['extension'] ?? 'pdf';

            // Determinar documento_id (FK a credito_docs_solicitados)
            $documentoId = $archivo['doc_id'] ?? null;
            if (!$documentoId && !empty($archivo['tipo'])) {
                $docSolicitado = CreditoDocsSolicitados::where('nombre', 'LIKE', '%' . $archivo['tipo'] . '%')->first();
                $documentoId = $docSolicitado?->id;
            }

            // CASO 1: Archivo nuevo enviado en Base64
            if (!empty($archivo['base64'])) {
                // 1.1 Guardar archivo para la solicitud de crédito en S3
                $relativePath = $this->saveDoc($archivo['base64'], $folder);
                $ext = $archivo['extension'] ?? pathinfo($relativePath, PATHINFO_EXTENSION);

                $guardados[] = CreditoDocs::create([
                    'solicitud_id' => $solicitud->id,
                    'documento_id' => $documentoId,
                    'path'         => $relativePath,
                    'extension'    => $ext,
                    'uploaded_by'  => $empleadoId,
                ]);

                // 1.2 Actualizar / Crear también en el expediente del cliente (cliente_docs)
                if ($cliente) {
                    $cleanedName = trim(mb_strtolower($nombreArchivo, 'UTF-8'));
                    $unaccentedName = iconv('UTF-8', 'ASCII//TRANSLIT//IGNORE', $cleanedName);

                    $estatusDoc = Estatus::where('tipo_estatus', 'TypeDocs')
                        ->where(function ($q) use ($nombreArchivo, $cleanedName, $unaccentedName) {
                            $q->where('nombre', 'LIKE', '%' . $nombreArchivo . '%')
                                ->orWhere('nombre', 'LIKE', '%' . $cleanedName . '%')
                                ->orWhere('nombre', 'LIKE', '%' . ($unaccentedName ?: $cleanedName) . '%');
                        })->first();

                    if ($estatusDoc) {
                        $clienteFolder = "intranet/cliente/id_" . ($cliente->rfc ?: $cliente->id);
                        $clientePath = $this->saveDoc($archivo['base64'], $clienteFolder);

                        $clienteDoc = ClientesDoc::where('cliente_id', $cliente->id)
                            ->where('status_id', $estatusDoc->id)
                            ->first();

                        $fechaVenc = !empty($archivo['expiration_date'])
                            ? $archivo['expiration_date']
                            : Carbon::now()->addMonths(3)->format('Y-m-d');

                        if ($clienteDoc) {
                            if (!empty($clienteDoc->path)) {
                                Storage::disk('s3')->delete($clienteDoc->path);
                            }
                            $clienteDoc->update([
                                'name'            => $archivo['nombre'] ?? ($nombreArchivo . '.' . $ext),
                                'path'            => $clientePath,
                                'extension'       => $ext,
                                'expiration_date' => $fechaVenc,
                            ]);
                        } else {
                            ClientesDoc::create([
                                'cliente_id'      => $cliente->id,
                                'status_id'       => $estatusDoc->id,
                                'name'            => $archivo['nombre'] ?? ($nombreArchivo . '.' . $ext),
                                'path'            => $clientePath,
                                'extension'       => $ext,
                                'expiration_date' => $fechaVenc,
                            ]);
                        }
                    }
                }

                CreditoHistorical::create([
                    'solicitud_id' => $solicitud->id,
                    'estatus_id'   => Estatus::where('nombre', 'Documento Adjuntado')->where('tipo_estatus', 'credito-interno')->first()->id,
                    'descripcion'  => "Documento: " . $nombreArchivo . ' adjuntado y actualizado en expediente.',
                    'empleado_id'  => $empleadoId
                ]);
            }
            // CASO 2: Archivo existente en el expediente del cliente (copiar archivo a la carpeta de la solicitud)
            elseif (!empty($archivo['existente']) && !empty($archivo['path'])) {
                $sourcePath = $archivo['path'];
                $ext = $archivo['extension'] ?? pathinfo($sourcePath, PATHINFO_EXTENSION);
                if (empty($ext)) {
                    $ext = 'pdf';
                }

                $fileName = Str::random(40) . '.' . $ext;
                $targetPath = $folder . '/' . $fileName;

                // Copiar el archivo en S3 a la carpeta de la solicitud de crédito
                if (Storage::disk('s3')->exists($sourcePath)) {
                    Storage::disk('s3')->copy($sourcePath, $targetPath);
                    $pathGuardado = $targetPath;
                } else {
                    $pathGuardado = $sourcePath;
                }

                $guardados[] = CreditoDocs::create([
                    'solicitud_id' => $solicitud->id,
                    'documento_id' => $documentoId,
                    'path'         => $pathGuardado,
                    'extension'    => $ext,
                    'uploaded_by'  => $empleadoId,
                ]);

                CreditoHistorical::create([
                    'solicitud_id' => $solicitud->id,
                    'estatus_id'   => Estatus::where('nombre', 'Documento Adjuntado')->where('tipo_estatus', 'credito-interno')->first()->id,
                    'descripcion'  => "Documento: " . $nombreArchivo . ' copiado desde el expediente del cliente a la solicitud.',
                    'empleado_id'  => $empleadoId
                ]);
            }
        }
        return $guardados;
    }
}
