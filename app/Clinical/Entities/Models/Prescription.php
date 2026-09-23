<?php

namespace App\Clinical\Entities\Models;

use App\Patient\Entities\Models\Patient;
use App\Staff\Entities\Models\Physician;
use Illuminate\Database\Eloquent\Attributes\Table;
use Illuminate\Database\Eloquent\Concerns\HasUlids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Carbon;

/**
 * @property string $id
 * @property string $physician_id
 * @property string $patient_id
 * @property string $medication_id
 * @property Carbon $prescribedDate
 * @property string|null $dose
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * @property Carbon|null $deleted_at
 */
#[Table(name: 'Prescribes')]
class Prescription extends Model
{
    use HasUlids, SoftDeletes;

    protected $guarded = [];

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'prescribedDate' => 'date',
        ];
    }

    /**
     * @return BelongsTo<Physician, $this>
     */
    public function physician(): BelongsTo
    {
        return $this->belongsTo(Physician::class);
    }

    /**
     * @return BelongsTo<Patient, $this>
     */
    public function patient(): BelongsTo
    {
        return $this->belongsTo(Patient::class);
    }

    /**
     * @return BelongsTo<Medication, $this>
     */
    public function medication(): BelongsTo
    {
        return $this->belongsTo(Medication::class);
    }
}
