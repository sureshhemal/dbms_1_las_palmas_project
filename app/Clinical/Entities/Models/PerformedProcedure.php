<?php

namespace App\Clinical\Entities\Models;

use App\Clinical\IO\Database\QueryBuilders\PerformedProceduresQueryBuilder;
use App\Patient\Entities\Models\Patient;
use App\Staff\Entities\Models\Nurse;
use App\Staff\Entities\Models\Physician;
use Database\Factories\Clinical\PerformedProcedureFactory;
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
 * @property string $patient_id
 * @property string $procedure_id
 * @property string $stay_id
 * @property Carbon $procDate
 * @property string $physician_id
 * @property string $nurse_id
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * @property Carbon|null $deleted_at
 */
#[Table(name: 'Undergoes')]
#[UseFactory(PerformedProcedureFactory::class)]
class PerformedProcedure extends Model
{
    use HasFactory, HasUlids, SoftDeletes;

    protected $guarded = [];

    public static function query(): PerformedProceduresQueryBuilder
    {
        return (new self)->newQuery();
    }

    public function newEloquentBuilder($query): PerformedProceduresQueryBuilder
    {
        return new PerformedProceduresQueryBuilder($query);
    }

    protected function casts(): array
    {
        return [
            'procDate' => 'date:Y-m-d',
        ];
    }

    public function patient(): BelongsTo
    {
        return $this->belongsTo(Patient::class)->withoutGlobalScope(SoftDeletingScope::class);
    }

    public function procedure(): BelongsTo
    {
        return $this->belongsTo(Procedure::class)->withoutGlobalScope(SoftDeletingScope::class);
    }

    public function stay(): BelongsTo
    {
        return $this->belongsTo(Stay::class)->withoutGlobalScope(SoftDeletingScope::class);
    }

    public function physician(): BelongsTo
    {
        return $this->belongsTo(Physician::class)->withoutGlobalScope(SoftDeletingScope::class);
    }

    public function nurse(): BelongsTo
    {
        return $this->belongsTo(Nurse::class)->withoutGlobalScope(SoftDeletingScope::class);
    }
}
