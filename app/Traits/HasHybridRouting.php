<?php

declare(strict_types=1);

namespace App\Traits;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

trait HasHybridRouting
{
    /**
     * Retrieve the model for a bound value.
     *
     * @param  \Illuminate\Database\Eloquent\Builder|\Illuminate\Database\Eloquent\Relations\Relation  $query
     * @param  mixed  $value
     * @param  string|null  $field
     * @return \Illuminate\Database\Eloquent\Relations\Relation|\Illuminate\Database\Eloquent\Builder
     */
    public function resolveRouteBindingQuery($query, $value, $field = null)
    {
        if ($field) {
            return parent::resolveRouteBindingQuery($query, $value, $field);
        }

        // If the value is a valid UUID, query by primary key
        if (Str::isUuid($value)) {
            return $query->where($this->getKeyName(), $value);
        }

        // Otherwise, query by the route key (slug)
        return $query->where($this->getRouteKeyName(), $value);
    }

    /**
     * Retrieve the model for a bound value.
     *
     * @param  mixed  $value
     * @param  string|null  $field
     * @return Model|null
     */
    public function resolveRouteBinding($value, $field = null)
    {
        if ($field) {
            return parent::resolveRouteBinding($value, $field);
        }

        // If the value is a valid UUID, query by primary key
        if (Str::isUuid($value)) {
            return $this->where($this->getKeyName(), $value)->first();
        }

        // Otherwise, query by the route key (slug)
        return $this->where($this->getRouteKeyName(), $value)->first();
    }
}
