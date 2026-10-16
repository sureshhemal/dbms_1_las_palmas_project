<?php

namespace App\Clinical\Entities\Models;

use App\Patient\Entities\Models\Patient;
use App\Staff\Entities\Models\Physician;
use Database\Factories\Clinical\PrescriptionFactory;
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
 * @property string $physician_id
 * @property string $patient_id
 * @property string $medication_id
 * @property Carbon $prescribedDate
 * @property string $dose
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * @property Carbon|null $deleted_at
 */
#[Table(name: 'Prescribes')]
#[UseFactory(PrescriptionFactory::class)]
class Prescription extends Model
{
    use HasFactory, HasUlids, SoftDeletes;

    protected $guarded = [];

    protected function casts(): array
    {
        return [
            'prescribedDate' => 'date:Y-m-d',
        ];
    }

    public function physician(): BelongsTo
    {
        return $this->belongsTo(Physician::class)->withoutGlobalScope(SoftDeletingScope::class);
    }

    public function patient(): BelongsTo
    {
        return $this->belongsTo(Patient::class)->withoutGlobalScope(SoftDeletingScope::class);
    }

    public function medication(): BelongsTo
    {
        return $this->belongsTo(Medication::class)->withoutGlobalScope(SoftDeletingScope::class);
    }
}
