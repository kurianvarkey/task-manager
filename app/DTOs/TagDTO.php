<?php

declare(strict_types=1);

namespace App\DTOs;

use InvalidArgumentException;

class TagDTO
{
    /**
     * The HTTP method.
     */
    private static $httpMethod;

    /**
     * Create a new instance.
     */
    public function __construct(
        public string $name,
        public ?string $color = null
    ) {}

    /**
     * Create a new instance from an array.
     */
    public static function fromArray(array $data, string $httpMethod): self
    {
        self::$httpMethod = $httpMethod;

        $name = $data['name'] ?? null;
        if (self::$httpMethod === 'POST' && empty($name)) {
            throw new InvalidArgumentException('Name is required');
        }

        return new self(
            name: $name,
            color: $data['color'] ?? null
        );
    }

    /**
     * Convert the object to its array representation.
     */
    public function toArray(): array
    {
        return self::$httpMethod === 'PUT'
        ? array_filter(get_object_vars($this), fn ($value) => ! is_null($value))
        : get_object_vars($this);
    }
}
