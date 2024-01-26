<?php

namespace App\Enums;

use Exception;

class AbsenceReason
{
    private const VACATION = 'vacation';
    private const VACATION_DAY = 'vacation_day';
    private const TIME_OFF = 'time_off';
    private const FLEXTIME = 'flextime';
    private const SICK_LEAVE = 'sick_leave';
    private const PERSONAL_LEAVE = 'personal_leave';
    private const TIME_OFF_IN_LIEU = 'time_off_in_lieu';
    private const OTHER = 'other';

    /**
     * @var AbsenceReason[]
     */
    private static $values = null;
    /**
     * @var string
     */
    private $reason;
    /**
     * @var string
     */
    private $displayValue;

    public function __construct(string $reason, string $displayValue = null)
    {
        $this->reason = $reason;
        $this->displayValue = $displayValue;
    }

    /**
     * @param string $reason
     * @return AbsenceReason
     * @throws Exception
     */
    public static function fromReason(string $reason): AbsenceReason
    {
        foreach (self::values() as $absenceReason) {
            if ($absenceReason->getReason() === $reason) {
                return $absenceReason;
            }
        }
        throw new Exception('Unknown absence reason: ' . $reason);
    }

    /**
     * @param string $displayValue
     * @return AbsenceReason
     * @throws Exception
     */
    public static function fromDisplayValue($displayValue)
    {
        foreach (self::values() as $absenceReason) {
            if ($absenceReason->getDisplayValue() === $displayValue) {
                return $absenceReason;
            }
        }
        throw new Exception('Unknown absence reason display value: ' . $displayValue);
    }

    /**
     * @return AbsenceReason[]
     */
    public static function values(): array
    {
        if (is_null(self::$values)) {
            self::$values = [
                self::SICK_LEAVE => new AbsenceReason(self::SICK_LEAVE, 'Enfermedad'),
                self::PERSONAL_LEAVE => new AbsenceReason(self::PERSONAL_LEAVE, 'Permiso Personal'),
                self::VACATION => new AbsenceReason(self::VACATION, 'Vacaciones'),
                self::VACATION_DAY => new AbsenceReason(self::VACATION_DAY, 'Dia de Vacacion'),
                self::TIME_OFF => new AbsenceReason(self::TIME_OFF, 'Descanso'),
                self::TIME_OFF_IN_LIEU => new AbsenceReason(self::TIME_OFF, 'Descanso con Paga'),
                self::FLEXTIME => new AbsenceReason(self::FLEXTIME, 'Medio Tiempo'),
                self::OTHER => new AbsenceReason(self::OTHER, 'Otro'),
            ];
        }
        return self::$values;
    }

    /**
     * @return AbsenceReason
     */
    public static function vacation(): AbsenceReason
    {
        return self::values()[self::VACATION];
    }

    /**
     * @return AbsenceReason
     */
    public static function vacationDay(): AbsenceReason
    {
        return self::values()[self::VACATION_DAY];
    }

    /**
     * @return AbsenceReason
     */
    public static function sickLeave(): AbsenceReason
    {
        return self::values()[self::SICK_LEAVE];
    }

    /**
     * @return AbsenceReason
     */
    public static function timeOff(): AbsenceReason
    {
        return self::values()[self::TIME_OFF];
    }

    /**
     * @return AbsenceReason
     */
    public static function timeOffInLieu(): AbsenceReason
    {
        return self::values()[self::TIME_OFF_IN_LIEU];
    }


    /**
     * @return AbsenceReason
     */
    public static function personalLeave(): AbsenceReason
    {
        return self::values()[self::PERSONAL_LEAVE];
    }

    /**
     * @return AbsenceReason
     */
    public static function flextime(): AbsenceReason
    {
        return self::values()[self::FLEXTIME];
    }

    /**
     * @return AbsenceReason
     */
    public static function other(): AbsenceReason
    {
        return self::values()[self::OTHER];
    }

    /**
     * @return string
     */
    public function getReason(): string
    {
        return $this->reason;
    }

    /**
     * @return string
     */
    public function getDisplayValue(): string
    {
        return $this->displayValue;
    }

    /**
     * @return string
     */
    public function __toString()
    {
        return (string) $this->reason;
    }

    /**
     * @return string
     */
    public static function toArray(): array
    {
        $values = array_map(function (AbsenceReason $absenceReason) {
            return [
                'label' => $absenceReason->getDisplayValue(),
                'value' => $absenceReason->getReason(),
            ];
        }, self::values());

        return array_values($values);
    }
}
