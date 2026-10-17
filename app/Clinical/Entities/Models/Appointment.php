<?php

namespace App\Clinical\Entities\Models;

use App\Clinical\IO\Database\QueryBuilders\AppointmentsQueryBuilder;
use App\Foundation\Entities\Concerns\HasAutoIncrementingColumn;
use App\Patient\Entities\Models\Patient;
use App\Staff\Entities\Models\Nurse;
use App\Staff\Entities\Models\Physician;
use Database\Factories\Clinical\AppointmentFactory;
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
 * @property int $appID
 * @property string $patient_id
 * @property string $nurse_id
 * @property string $physician_id
 * @property Carbon $startDateTime
 * @property Carbon $endDateTime
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * @property Carbon|null $deleted_at
 */
#[Table(name: 'Appointment')]
#[UseFactory(AppointmentFactory::class)]
class Appointment extends Model
{
    use HasAutoIncrementingColumn, HasFactory, HasUlids, SoftDeletes;

    protected string $autoIncrementingColumn = 'appID';

    protected $guarded = [];

    public static function query(): AppointmentsQueryBuilder
    {
        return (new self)->newQuery();
    }

    public function newEloquentBuilder($query): AppointmentsQueryBuilder
    {
        return new AppointmentsQueryBuilder($query);
    }

    protected function casts(): array
    {
        return [
            'startDateTime' => 'datetime',
            'endDateTime' => 'datetime',
        ];
    }

    public function patient(): BelongsTo
    {
        return $this->belongsTo(Patient::class)->withoutGlobalScope(SoftDeletingScope::class);
    }

    public function nurse(): BelongsTo
    {
        return $this->belongsTo(Nurse::class)->withoutGlobalScope(SoftDeletingScope::class);
    }

    public function physician(): BelongsTo
    {
        return $this->belongsTo(Physician::class)->withoutGlobalScope(SoftDeletingScope::class);
    }
}
