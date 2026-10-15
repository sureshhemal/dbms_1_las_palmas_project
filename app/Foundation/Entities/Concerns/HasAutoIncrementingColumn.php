<?php

namespace App\Foundation\Entities\Concerns;

trait HasAutoIncrementingColumn
{
    public static function bootHasAutoIncrementingColumn(): void
    {
        static::created(function (self $model): void {
            $column = $model->getAutoIncrementingColumn();

            if ($model->getAttribute($column) !== null) {
                return;
            }

            $model->setAttribute($column, (int) $model->getConnection()->getPdo()->lastInsertId());
            $model->syncOriginalAttribute($column);
        });
    }

    public function getAutoIncrementingColumn(): string
    {
        return $this->autoIncrementingColumn;
    }
}
