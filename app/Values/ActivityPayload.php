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
class ActivityPayload implements \JsonSerializable, Arrayable, Castable, Jsonable
{
    /**
     * @param  array<array-key, mixed>  $attributes
     */
    public function __construct(
        protected array $attributes = []
    ) {
        foreach ($attributes as $key => $value) {
            if (! is_string($key)) {
                throw new \InvalidArgumentException('ActivityPayload attributes keys must be strings.');
            }

            if (is_array($value)) {
                $keys = array_keys($value);
                $diff = array_diff($keys, ['from', 'to']);
                if (! empty($diff)) {
                    throw new \InvalidArgumentException("ActivityPayload nested array for key '{$key}' can only contain 'from' and 'to'.");
                }
            }
        }
    }

    /**
     * @return string
     */
    public static function castUsing(array $arguments)
    {
        return ActivityPayloadCast::class;
    }

    /**
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
        /** @var array<string, mixed> $attributes */
        $attributes = $this->attributes;

        return $attributes;
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
 * @implements CastsAttributes<ActivityPayload, mixed>
 */
class ActivityPayloadCast implements CastsAttributes
{
    /**
     * @param  array<string, mixed>  $attributes
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
     * @param  array<string, mixed>  $attributes
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
