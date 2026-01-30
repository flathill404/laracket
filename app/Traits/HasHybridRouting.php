<?php

declare(strict_types=1);

namespace App\Traits;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\Relation;
use Illuminate\Support\Str;

trait HasHybridRouting
{
    /**
     * Retrieve the model for a bound value.
     *
     * @param  \Illuminate\Database\Eloquent\Builder<Model>|\Illuminate\Database\Eloquent\Relations\Relation<Model, Model, mixed>  $query
     * @param  mixed  $value
     * @return \Illuminate\Database\Eloquent\Relations\Relation<Model, Model, mixed>|\Illuminate\Contracts\Database\Eloquent\Builder
     */
    public function resolveRouteBindingQuery($query, $value, $field = null)
    {
        if ($field) {
            return parent::resolveRouteBindingQuery($query, $value, $field);
        }

        // If the value is a valid UUID, query by primary key
        if (Str::isUuid($value)) {
            return $query->where($this->qualifyColumn($this->getKeyName()), $value);
        }

        // Otherwise, query by the route key (slug)
        return $query->where($this->qualifyColumn($this->getRouteKeyName()), $value);
    }

    /**
     * Retrieve the child model for a bound value.
     *
     * @param  string  $childType
     * @param  mixed  $value
     * @param  string|null  $field
     * @return \Illuminate\Database\Eloquent\Model|null
     */
    public function resolveChildRouteBinding($childType, $value, $field)
    {
        if ($field) {
            return parent::resolveChildRouteBinding($childType, $value, $field);
        }

        /** @var \Illuminate\Database\Eloquent\Relations\Relation<Model, Model, mixed> $relationship */
        $relationship = $this->{Str::plural(Str::camel($childType))}();

        $query = $relationship->getRelated()->resolveRouteBindingQuery($relationship, $value, null);

        // @phpstan-ignore instanceof.alwaysTrue
        return $relationship instanceof Relation
            ? $query->firstOrFail()
            : $query->first(); // @phpstan-ignore method.nonObject
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
