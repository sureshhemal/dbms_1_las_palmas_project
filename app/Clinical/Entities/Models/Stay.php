<?php

namespace App\Clinical\Entities\Models;

use App\Clinical\IO\Database\QueryBuilders\StaysQueryBuilder;
use App\Foundation\Entities\Concerns\HasAutoIncrementingColumn;
use App\Hospital\Entities\Models\Room;
use App\Patient\Entities\Models\Patient;
use Database\Factories\Clinical\StayFactory;
use Illuminate\Database\Eloquent\Attributes\Table;
use Illuminate\Database\Eloquent\Attributes\UseFactory;
use Illuminate\Database\Eloquent\Concerns\HasUlids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use Illuminate\Support\Carbon;

/**
 * @property string $id
 * @property int $stayID
 * @property string $patient_id
 * @property string $room_id
 * @property Carbon $startDate
 * @property Carbon|null $endDate
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * @property Carbon|null $deleted_at
 */
#[Table(name: 'Stay')]
#[UseFactory(StayFactory::class)]
class Stay extends Model
{
    use HasAutoIncrementingColumn, HasFactory, HasUlids, SoftDeletes;

    protected string $autoIncrementingColumn = 'stayID';

    protected $guarded = [];

    public static function query(): StaysQueryBuilder
    {
        return (new self)->newQuery();
    }

    public function newEloquentBuilder($query): StaysQueryBuilder
    {
        return new StaysQueryBuilder($query);
    }

    protected function casts(): array
    {
        return [
            'startDate' => 'date:Y-m-d',
            'endDate' => 'date:Y-m-d',
        ];
    }

    public function patient(): BelongsTo
    {
        return $this->belongsTo(Patient::class)->withoutGlobalScope(SoftDeletingScope::class);
    }

    public function room(): BelongsTo
    {
        return $this->belongsTo(Room::class)->withoutGlobalScope(SoftDeletingScope::class);
    }
}
