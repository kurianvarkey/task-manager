<?php

declare(strict_types=1);

namespace App\Enums\Traits;

trait EnumCommonFunctions
{
    /**
     * Get all keys of the enum
     */
    public static function getValues(): array
    {
        return array_map('strtolower', array_column(self::cases(), 'name'));
    }

    /**
     * Get the enum from its string value.
     */
    public static function fromString(?string $name): ?self
    {
        // @codeCoverageIgnoreStart
        if (is_null($name)) {
            return null;
        }

        $items = array_map('strtolower', array_column(self::cases(), 'name', 'value'));
        if (! $item = array_search($name, $items, true)) {
            return null;
        }
        // @codeCoverageIgnoreEnd

        return self::from($item);
    }
}
