<?php

namespace App\Clinical\Entities\Models;

use App\Foundation\Entities\Concerns\HasAutoIncrementingColumn;
use App\Hospital\Entities\Models\Room;
use App\Patient\Entities\Models\Patient;
use Illuminate\Database\Eloquent\Attributes\Table;
use Illuminate\Database\Eloquent\Concerns\HasUlids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;
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
class Stay extends Model
{
    use HasAutoIncrementingColumn, HasUlids, SoftDeletes;

    protected string $autoIncrementingColumn = 'stayID';

    protected $guarded = [];

    protected function casts(): array
    {
        return [
            'startDate' => 'date',
            'endDate' => 'date',
        ];
    }

    public function patient(): BelongsTo
    {
        return $this->belongsTo(Patient::class);
    }

    public function room(): BelongsTo
    {
        return $this->belongsTo(Room::class);
    }
}
