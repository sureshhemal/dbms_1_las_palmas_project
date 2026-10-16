<?php

namespace App\Hospital\Entities\Models;

use App\Clinical\Entities\Models\Stay;
use App\Foundation\Entities\Concerns\HasAutoIncrementingColumn;
use App\Hospital\Entities\HospitalCalendar;
use App\Hospital\Entities\RoomType;
use Database\Factories\Hospital\RoomFactory;
use Illuminate\Database\Eloquent\Attributes\Table;
use Illuminate\Database\Eloquent\Attributes\UseFactory;
use Illuminate\Database\Eloquent\Concerns\HasUlids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Carbon;

/**
 * @property string $id
 * @property int $roomID
 * @property RoomType $roomType
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * @property Carbon|null $deleted_at
 */
#[Table(name: 'Room')]
#[UseFactory(RoomFactory::class)]
class Room extends Model
{
    use HasAutoIncrementingColumn, HasFactory, HasUlids, SoftDeletes;

    protected string $autoIncrementingColumn = 'roomID';

    protected $guarded = [];

    protected function casts(): array
    {
        return [
            'roomType' => RoomType::class,
        ];
    }

    public function stays(): HasMany
    {
        return $this->hasMany(Stay::class);
    }

    public function currentStays(): HasMany
    {
        return $this->hasMany(Stay::class)->whereOccupyingABedOn(HospitalCalendar::today())->oldest('startDate');
    }
}
