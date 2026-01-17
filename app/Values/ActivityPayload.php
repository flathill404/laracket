<?php

namespace App\Values;

use Illuminate\Contracts\Database\Eloquent\Castable;
use Illuminate\Contracts\Database\Eloquent\CastsAttributes;
use Illuminate\Contracts\Support\Arrayable;
use Illuminate\Contracts\Support\Jsonable;
use Illuminate\Database\Eloquent\Model;

/**
 * @implements Arrayable<string, mixed>
 */
class ActivityPayload implements Castable, Arrayable, Jsonable, \JsonSerializable
{
    /**
     * @param array<string, mixed> $attributes
     */
    public function __construct(
        protected array $attributes = []
    ) {
    }

    /**
     * @return string
     */
    public static function castUsing(array $arguments)
    {
        return ActivityPayloadCast::class;
    }

    /**
     * @param string $key
     * @return mixed
     */
    public function __get(string $key)
    {
        return $this->attributes[$key] ?? null;
    }

    /**
     * @return array<string, mixed>
     */
    public function toArray()
    {
        return $this->attributes;
    }

    public function toJson($options = 0)
    {
        return (string) json_encode($this->attributes, $options);
    }
    
    public function jsonSerialize(): mixed
    {
        return $this->attributes;
    }
}

/**
 * @implements CastsAttributes<ActivityPayload, ActivityPayload>
 */
class ActivityPayloadCast implements CastsAttributes
{
    /**
     * @param Model $model
     * @param string $key
     * @param mixed $value
     * @param array<string, mixed> $attributes
     */
    public function get(Model $model, string $key, mixed $value, array $attributes): ActivityPayload
    {
        $json = is_string($value) ? $value : '{}';
        $data = json_decode($json, true);
        
        if (! is_array($data)) {
            $data = [];
        }

        /** @var array<string, mixed> $data */
        return new ActivityPayload($data);
    }

    /**
     * @param Model $model
     * @param string $key
     * @param mixed $value
     * @param array<string, mixed> $attributes
     */
    public function set(Model $model, string $key, mixed $value, array $attributes): ?string
    {
        if (is_null($value)) {
            return null;
        }

        if ($value instanceof ActivityPayload) {
            return $value->toJson();
        }

        if (is_array($value)) {
            return (string) json_encode($value);
        }

        throw new \InvalidArgumentException('The given value is not an ActivityPayload instance or array.');
    }
}
