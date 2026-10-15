<?php

namespace App\Clinical\Entities\Models;

use App\Foundation\Entities\Concerns\HasAutoIncrementingColumn;
use App\Patient\Entities\Models\Patient;
use App\Staff\Entities\Models\Nurse;
use App\Staff\Entities\Models\Physician;
use Illuminate\Database\Eloquent\Attributes\Table;
use Illuminate\Database\Eloquent\Concerns\HasUlids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Carbon;

/**
 * @property string $id
 * @property int $appID
 * @property string $patient_id
 * @property string $nurse_id
 * @property string $physician_id
 * @property Carbon $startDateTime
 * @property Carbon|null $endDateTime
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * @property Carbon|null $deleted_at
 */
#[Table(name: 'Appointment')]
class Appointment extends Model
{
    use HasAutoIncrementingColumn, HasUlids, SoftDeletes;

    protected string $autoIncrementingColumn = 'appID';

    protected $guarded = [];

    protected function casts(): array
    {
        return [
            'startDateTime' => 'datetime',
            'endDateTime' => 'datetime',
        ];
    }

    public function patient(): BelongsTo
    {
        return $this->belongsTo(Patient::class);
    }

    public function nurse(): BelongsTo
    {
        return $this->belongsTo(Nurse::class);
    }

    public function physician(): BelongsTo
    {
        return $this->belongsTo(Physician::class);
    }
}
