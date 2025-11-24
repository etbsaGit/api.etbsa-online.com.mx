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

    public function scopeFilterSearchVacation(Builder $query, array $filters, array $searchColumns = [])
    {
        foreach ($filters as $key => $value) {
            if ($key !== 'page') {
                if ($key === 'search' && !empty($searchColumns) && $value !== null) {
                    $query->where(function ($query) use ($value, $searchColumns) {
                        foreach ($searchColumns as $column) {
                            $query->orWhere($column, 'LIKE', '%' . $value . '%');
                        }
                    });
                } elseif ($key === 'validated' && $value === null) {
                    $query->whereNull('validated');
                } elseif ($value !== null) {
                    $query->where($key, $value);
                }
            }
        }

        return $query;
    }

    public function scopeFilterSearchPermiso(Builder $query, array $filters, array $searchColumns = [])
    {
        foreach ($filters as $key => $value) {
            if ($key !== 'page') {
                if ($key === 'search' && !empty($searchColumns) && $value !== null) {
                    $query->where(function ($query) use ($value, $searchColumns) {
                        foreach ($searchColumns as $column) {
                            $query->orWhere($column, 'LIKE', '%' . $value . '%');
                        }
                    });
                } elseif ($key === 'status' && $value === null) {
                    $query->whereNull('status');
                } elseif ($key === 'month' && $value !== null) {
                    // Filtra por mes de la columna 'date'
                    $query->whereMonth('date', $value);
                } elseif ($value !== null) {
                    $query->where($key, $value);
                }
            }
        }

        return $query;
    }

    public function scopeFilterPost(Builder $query, array $filters)
    {
        // Asegurarse de que no traiga registros con departamento_id null
        $query->whereNotNull('departamento_id');

        if (isset($filters['departamento_id']) && $filters['departamento_id'] !== null) {
            // Aplicar el filtro principal de departamento_id
            $query->where('departamento_id', $filters['departamento_id']);

            // Remover el filtro de departamento_id del array para evitar duplicados
            unset($filters['departamento_id']);

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
