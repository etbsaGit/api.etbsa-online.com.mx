<?php

namespace App\Exports\ReporteClientes;

use App\Models\Intranet\ClienteRiego;
use App\Models\Intranet\ClienteTechnology;
use App\Models\Intranet\InversionesAgricola;
use App\Models\Intranet\Machine;

use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithDrawings;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\WithEvents;
use Maatwebsite\Excel\Concerns\WithCustomStartCell;
use Maatwebsite\Excel\Concerns\WithColumnFormatting;

use Maatwebsite\Excel\Events\AfterSheet;

use PhpOffice\PhpSpreadsheet\Worksheet\Drawing;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;
use PhpOffice\PhpSpreadsheet\Style\NumberFormat;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Style\Alignment;

class TechClienteExport implements
    FromCollection,
    WithHeadings,
    WithDrawings,
    ShouldAutoSize,
    WithStyles,
    WithEvents,
    WithCustomStartCell,
    WithColumnFormatting
{
    protected $filters;

    public function __construct($filters = [])
    {
        $this->filters = $filters;
    }

    /**
     * Logo
     */
    public function drawings()
    {
        $logoPath = public_path('img/etbsa-logo-agricola.png');

        if (!file_exists($logoPath)) {
            return [];
        }

        $drawing = new Drawing();

        $drawing->setName('ETBSA');
        $drawing->setDescription('Logo ETBSA');
        $drawing->setPath($logoPath);
        $drawing->setHeight(70);
        $drawing->setCoordinates('A1');

        return $drawing;
    }

    /**
     * Comenzar encabezados en fila 4
     */
    public function startCell(): string
    {
        return 'A4';
    }

    /**
     * Datos
     */
    public function collection()
    {
        return ClienteTechnology::query()
            ->filter($this->filters)
            ->with('cliente','nuevaTecnologia')
            ->get()
            ->map(function ($tech) {

                return [
                    'cliente' => $tech->cliente?->nombre ?? '',
                    'rfc' => $tech->cliente?->rfc ?? '',
                    'telefono' => $tech->cliente?->telefono ?? '',
                    'email' => $tech->cliente?->email ?? '',

                    'ubicacion' => trim(
                        ($tech->cliente?->calle ?? '') . ' ' .
                            ($tech->cliente?->colonia ?? '') . ' ' .
                            ($tech->cliente?->town?->name ?? '')
                    ),
                    'tecnologia' => $tech->nuevaTecnologia?->name ?? '',
                    'hconectadas' => $tech->cliente?->hectareasConectadas?->hectareas_conectadas ?? '',
                    'hpropias' => $tech->cliente?->hectareasConectadas?->hectareas_propias ?? '',
                    'hrentadas' => $tech->cliente?->hectareasConectadas?->hectareas_rentadas ?? '',
                    'hdesconectadas' => $tech->cliente?->hectareasConectadas?->hectareas_sin_conectar ?? '',
                    'adopcion' => $tech->cliente?->currentClassTech ?? '',

                    'vendedor_asignado' => $tech->cliente?->empleados
                        ?->pluck('nombreCompleto')
                        ->implode(', ') ?? '',
                ];
            });
    }

    /**
     * Encabezados
     */
    public function headings(): array
    {
        return [
            'Cliente',
            'RFC',
            'Teléfono',
            'Email',
            'Ubicación',
            'Tecnología',
            'Hectareas Conectadas',
            'Hectareas Propias',
            'Hectareas Rentadas',
            'Hectareas Sin Conectar',
            'Adopción Tecnológica',
            'Vendedor Asignado',
        ];
    }

    /**
     * Estilos encabezados
     */
    public function styles(Worksheet $sheet)
    {
        return [
            4 => [
                'font' => [
                    'bold' => true,
                    'color' => [
                        'rgb' => 'FFFFFF'
                    ]
                ],
                'fill' => [
                    'fillType' => Fill::FILL_SOLID,
                    'startColor' => [
                        'rgb' => '1F4E78'
                    ]
                ]
            ]
        ];
    }

    /**
     * Formatos de columnas
     */
    public function columnFormats(): array
    {
        return [
            // 'I' => '$#,##0.00',
        ];
    }

    /**
     * Eventos hoja
     */
    public function registerEvents(): array
    {
        return [
            AfterSheet::class => function (AfterSheet $event) {

                $sheet = $event->sheet;

                // Título
                $sheet->mergeCells('A1:F1');

                $sheet->setCellValue(
                    'A1',
                    'REPORTE DE TECNOLOGÍAS DE CLIENTES'
                );

                $sheet->setCellValue(
                    'C2',
                    'Generado: ' . now()->format('d/m/Y H:i')
                );

                // Estilos título
                $sheet->getStyle('A1')->getFont()
                    ->setBold(true)
                    ->setSize(18);

                $sheet->getStyle('A2')->getFont()
                    ->setItalic(true);

                $sheet->getStyle('A1')
                    ->getAlignment()
                    ->setHorizontal(
                        Alignment::HORIZONTAL_CENTER
                    );

                // Congelar encabezados
                $sheet->freezePane('A5');

                // Filtros Excel
                $sheet->setAutoFilter('A4:L4');
            }
        ];
    }
}
