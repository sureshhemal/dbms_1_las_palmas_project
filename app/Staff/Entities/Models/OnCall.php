<?php

namespace App\Staff\Entities\Models;

use Illuminate\Database\Eloquent\Attributes\Table;
use Illuminate\Database\Eloquent\Concerns\HasUlids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Carbon;

/**
 * @property string $id
 * @property string $nurse_id
 * @property Carbon $startDate
 * @property Carbon $endDate
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * @property Carbon|null $deleted_at
 */
#[Table(name: 'OnCall')]
class OnCall extends Model
{
    use HasUlids, SoftDeletes;

    protected $guarded = [];

    protected function casts(): array
    {
        return [
            'startDate' => 'date',
            'endDate' => 'date',
        ];
    }

    public function nurse(): BelongsTo
    {
        return $this->belongsTo(Nurse::class);
    }
}
