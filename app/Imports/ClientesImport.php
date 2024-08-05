<?php

namespace App\Imports;

use App\Models\Intranet\Cliente;
use Maatwebsite\Excel\Concerns\ToModel;
use Maatwebsite\Excel\Concerns\WithHeadingRow;

class ClientesImport implements ToModel, WithHeadingRow
{
    /**
     * @param array $row
     *
     * @return \Illuminate\Database\Eloquent\Model|null
     */
    public function model(array $row)
    {
        $equip = $this->sanitizeField($row['equip']);
        $nombre = $this->sanitizeField($row['nombre']);
        $tipo = $this->sanitizeField($row['tipo']);
        $rfc = $this->sanitizeField($row['rfc']);
        $curp = $this->sanitizeField($row['curp']);
        $telefono = $this->sanitizeField($row['telefono']);
        $telefono_casa = $this->sanitizeField($row['telefono_casa']);
        $email = $this->sanitizeField($row['email']);
        $state_entity_id = $this->sanitizeField($row['state_entity_id']);
        $town_id = $this->sanitizeField($row['town_id']);
        $colonia = $this->sanitizeField($row['colonia']);
        $calle = $this->sanitizeField($row['calle']);
        $codigo_postal = $this->sanitizeField($row['codigo_postal']);
        $classification_id = $this->sanitizeField($row['classification_id']);
        $segmentation_id = $this->sanitizeField($row['segmentation_id']);
        $technological_capability_id = $this->sanitizeField($row['technological_capability_id']);
        $tactic_id = $this->sanitizeField($row['tactic_id']);
        $construction_classification_id = $this->sanitizeField($row['construction_classification_id']);

        return new Cliente([
            'equip' => $equip,
            'nombre' => $nombre,
            'tipo' => $tipo,
            'rfc' => $rfc,
            'curp' => $curp,
            'telefono' => $telefono,
            'telefono_casa' => $telefono_casa,
            'email' => $email,
            'state_entity_id' => $state_entity_id,
            'town_id' => $town_id,
            'colonia' => $colonia,
            'calle' => $calle,
            'codigo_postal' => $codigo_postal,
            'classification_id' => $classification_id,
            'segmentation_id' => $segmentation_id,
            'technological_capability_id' => $technological_capability_id,
            'tactic_id' => $tactic_id,
            'construction_classification_id' => $construction_classification_id,
        ]);
    }

    /**
     * Limpia los campos para convertir espacios en blanco a NULL.
     *
     * @param mixed $value
     * @return mixed
     */
    private function sanitizeField($value)
    {
        // Convertir espacios en blanco a NULL
        return trim($value) === '' ? null : $value;
    }
}
