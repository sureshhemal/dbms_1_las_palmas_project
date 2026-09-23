<?php

namespace App\Foundation\Entities\Concerns;

/**
 * For backed enums: lists the backing values, e.g. for a check constraint in a migration.
 */
trait HasValues
{
    /**
     * @return list<string|int>
     */
    public static function values(): array
    {
        return array_column(self::cases(), 'value');
    }
}
