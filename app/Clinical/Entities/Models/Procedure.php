<?php

namespace App\Clinical\Entities\Models;

use App\Foundation\Entities\Concerns\HasAutoIncrementingColumn;
use Illuminate\Database\Eloquent\Attributes\Table;
use Illuminate\Database\Eloquent\Concerns\HasUlids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Carbon;

/**
 * @property string $id
 * @property int $procID
 * @property string $name
 * @property float|null $cost
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * @property Carbon|null $deleted_at
 */
#[Table(name: 'Procedure')]
class Procedure extends Model
{
    use HasAutoIncrementingColumn, HasUlids, SoftDeletes;

    protected string $autoIncrementingColumn = 'procID';

    protected $guarded = [];

    protected function casts(): array
    {
        return [
            'cost' => 'float',
        ];
    }
}
