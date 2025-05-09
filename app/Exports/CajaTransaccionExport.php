<?php

namespace App\Exports;

use App\Models\Caja\CajaTransaccion;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;


class CajaTransaccionExport implements FromCollection, WithHeadings, WithMapping, ShouldAutoSize
{
    protected $fecha;
    protected $userId;

    public function __construct($fecha, $userId)
    {
        $this->fecha = $fecha;
        $this->userId = $userId;
    }

    /**
     * @return \Illuminate\Support\Collection
     */
    public function collection()
    {
        return CajaTransaccion::with(['cliente', 'user', 'tipoFactura', 'cuenta.cajaBanco', 'tipoPago'])
            ->whereDate('created_at', $this->fecha)
            ->where('user_id', $this->userId)
            ->get();
    }

    public function map($transaccion): array
    {
        return [
            $transaccion->factura,
            $transaccion->fecha_pago,
            $transaccion->folio,
            $transaccion->serie,
            $transaccion->uuid,
            $transaccion->total,
            optional($transaccion->cliente)->nombre,
            optional($transaccion->tipoFactura)->nombre,
            optional($transaccion->cuenta->cajaBanco)->nombre,
            optional($transaccion->cuenta)->numeroCuenta,
            optional($transaccion->tipoPago)->nombre,
        ];
    }

    public function headings(): array
    {
        return [
            'Factura',
            'Fecha de Pago',
            'Folio',
            'Serie',
            'UUID',
            'Total',
            'Cliente',
            'Tipo de Factura',
            'Banco',
            'Cuenta',
            'Tipo de Pago',
        ];
    }
}
