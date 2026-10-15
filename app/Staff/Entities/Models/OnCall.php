<?php

namespace App\Staff\Entities\Models;

use App\Staff\IO\Database\QueryBuilders\OnCallsQueryBuilder;
use Database\Factories\Staff\OnCallFactory;
use Illuminate\Database\Eloquent\Attributes\Table;
use Illuminate\Database\Eloquent\Attributes\UseFactory;
use Illuminate\Database\Eloquent\Concerns\HasUlids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Carbon;

/**
 * @property string $id
 * @property string $nurse_id
 * @property Carbon $startDate
 * @property Carbon $endDate
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 */
#[Table(name: 'OnCall')]
#[UseFactory(OnCallFactory::class)]
class OnCall extends Model
{
    use HasFactory, HasUlids;

    protected $guarded = [];

    public static function query(): OnCallsQueryBuilder
    {
        return (new self)->newQuery();
    }

    public function newEloquentBuilder($query): OnCallsQueryBuilder
    {
        return new OnCallsQueryBuilder($query);
    }

    protected function casts(): array
    {
        return [
            'startDate' => 'date:Y-m-d',
            'endDate' => 'date:Y-m-d',
        ];
    }

    public function nurse(): BelongsTo
    {
        return $this->belongsTo(Nurse::class);
    }
}
