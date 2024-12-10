<?php

namespace App\Traits;

use Illuminate\Database\Eloquent\Builder;

trait FilterableModel
{
    // -Final-
    public function scopeFilterSearch(Builder $query, array $filters, array $searchColumns = [])
    {
        foreach ($filters as $key => $value) {
            if ($value !== null && $key !== 'page') {
                if ($key === 'search' && !empty($searchColumns)) {
                    $query->where(function ($query) use ($value, $searchColumns) {
                        foreach ($searchColumns as $column) {
                            $query->orWhere($column, 'LIKE', '%' . $value . '%');
                        }
                    });
                } else {
                    // $query->where($key, 'LIKE', '%' . $value . '%');
                    $query->where($key, $value);
                }
            }
        }

        return $query;
    }

    public function scopeFilterPost(Builder $query, array $filters)
    {
        // Asegurarse de que no traiga registros con linea_id null
        $query->whereNotNull('linea_id');

        if (isset($filters['linea_id']) && $filters['linea_id'] !== null) {
            // Aplicar el filtro principal de linea_id
            $query->where('linea_id', $filters['linea_id']);

            // Remover el filtro de linea_id del array para evitar duplicados
            unset($filters['linea_id']);

            // Aplicar los demás filtros con orWhere dentro de un grupo
            $query->where(function ($subQuery) use ($filters) {
                foreach ($filters as $key => $value) {
                    if ($value !== null) {
                        $subQuery->orWhere($key, $value);
                    }
                }
            });
        }
    }

    public function scopeFilterByTravel(Builder $query, array $filters)
    {
        foreach ($filters as $key => $value) {
            if ($value !== null) {
                if ($key === 'start_point' || $key === 'end_point') {
                    $query->whereHas('travel', function ($query) use ($key, $value) {
                        if ($value === 'null') {
                            $query->whereNull($key);
                        } else {
                            $query->where($key, $value);
                        }
                    });
                } else {
                    $query->where($key, $value);
                }
            } else {
                // Si el valor es null, buscamos los eventos donde el travel tenga ese campo nulo
                if ($key === 'start_point' || $key === 'end_point') {
                    $query->whereHas('travel', function ($query) use ($key) {
                        $query->whereNull($key);
                    });
                }
            }
        }
    }

    public function scopeFilterByTravelAdmin(Builder $query, array $filters)
    {
        foreach ($filters as $key => $value) {
            if ($value !== null) { // Solo aplicar el filtro si el valor no es nulo
                if ($key === 'start_point' || $key === 'end_point') {
                    $query->whereHas('travel', function ($query) use ($key, $value) {
                        $query->where($key, $value); // Filtrar por 'start_point' o 'end_point'
                    });
                } else {
                    $query->where($key, $value); // Filtrar directamente para otros casos
                }
            }
        }
    }
}
