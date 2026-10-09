<?php

namespace App\Models;

use App\Traits\FilterableModel;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class VacationDiaCuentaBitacora extends Model
{
    use HasFactory, FilterableModel;
    protected $table = 'vacation_dia_cuenta_bitacora';

    protected $fillable = [
        'empleado_id',
        'dia_cuenta_id',
        'vacation_day_id',
        'estatus_id',
        'comentario'
    ];

    public const ESTATUS_SOLICITUD_CREADA = 'Solicitud creada';
    public const ESTATUS_SIN_DIAS = 'Solicitud NO creada (sin días suficientes)';
    public const ESTATUS_SOLICITUD_PREVIA = 'Solicitud NO creada (día solicitado previamente)';
    public const ESTATUS_INFO = 'Info';

    protected static array $estatusCache = [];

    public static function getEstatusId(string $nombre): ?int
    {
        if (isset(self::$estatusCache[$nombre])) {
            return self::$estatusCache[$nombre];
        }

        $id = Estatus::where('tipo_estatus', 'vacation-dia-cuenta')
            ->where('nombre', $nombre)
            ->value('id');

        if ($id) {
            self::$estatusCache[$nombre] = $id;
        }

        return $id;
    }

    public static function registrar(
        string $estatusNombre,
        string $comentario,
        ?int $diaCuentaId = null,
        ?int $empleadoId = null,
        ?int $vacationDayId = null
    ): ?self {
        $estatusId = self::getEstatusId($estatusNombre);

        return self::create([
            'estatus_id' => $estatusId,
            'comentario' => $comentario,
            'dia_cuenta_id' => $diaCuentaId,
            'empleado_id' => $empleadoId,
            'vacation_day_id' => $vacationDayId,
        ]);
    }

    public function empleado()
    {
        return $this->belongsTo(Empleado::class);
    }

    public function vacationDay()
    {
        return $this->belongsTo(VacationDay::class);
    }

    public function diaCuenta()
    {
        return $this->belongsTo(VacationDiaCuenta::class);
    }
    public function estatus()
    {
        return $this->belongsTo(Estatus::class);
    }
}
