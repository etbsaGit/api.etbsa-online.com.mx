<?php

namespace App\Traits;

use Illuminate\Database\Eloquent\Builder;

trait FilterableModel
{
    // public function scopeFilter($query, array $filters): void
    // {
    //     $query->when($filters['search'] ?? null, function ($query, $search) {
    //         $query->where(function ($query) use ($search) {
    //             $query->where('name', 'like', '%'.$search.'%');
    //         });
    //     })->when($filters['trashed'] ?? null, function ($query, $trashed) {
    //         if ($trashed === 'with') {
    //             $query->withTrashed();
    //         } elseif ($trashed === 'only') {
    //             $query->onlyTrashed();
    //         }
    //     });
    // }

    public function scopeFilter(Builder $query, array $filters)
    {
        foreach ($filters as $key => $value) {
            if ($value !== null) {
                $query->where($key, $value);
            }
        }
    }

    public function scopeFilterPost(Builder $query, array $filters)
    {
        $first = true; // Flag to check if it's the first filter

        foreach ($filters as $key => $value) {
            if ($value !== null) {
                if ($first) {
                    $query->where($key, $value);
                    $first = false;
                } else {
                    $query->orWhere($key, $value);
                }
            }
        }
    }


    public function scopeFilterPage(Builder $query, array $filters)
    {
        foreach ($filters as $key => $value) {
            if ($value !== null && $key !== 'page') {
                if ($key === 'search') {
                    $query->where(function ($query) use ($value) {
                        $query->where('nombre', 'LIKE', '%' . $value . '%')
                            ->orWhere('telefono', 'LIKE', '%' . $value . '%')
                            ->orWhere('rfc', 'LIKE', '%' . $value . '%');
                    });
                } else {
                    $query->where($key, 'LIKE', '%' . $value . '%');
                }
            }
        }
        return $query;
    }




    public function scopeFilterone(Builder $query, array $filters)
    {
        $query->where(function ($query) use ($filters) {
            foreach ($filters as $key => $value) {
                if ($value !== null) {
                    $query->orWhere($key, $value);
                }
            }
        });
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
}
