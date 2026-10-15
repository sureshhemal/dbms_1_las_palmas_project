<?php

namespace App\Patient\Entities\Models;

use App\Foundation\Entities\Concerns\HasAutoIncrementingColumn;
use App\Hospital\Entities\HospitalCalendar;
use App\Staff\Entities\Models\Physician;
use Database\Factories\Patient\PatientFactory;
use Illuminate\Database\Eloquent\Attributes\Table;
use Illuminate\Database\Eloquent\Attributes\UseFactory;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Concerns\HasUlids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use Illuminate\Support\Carbon;

/**
 * @property string $id
 * @property int $patientID
 * @property string|null $ssn
 * @property string $name
 * @property string|null $address
 * @property Carbon|null $dob
 * @property string|null $phone
 * @property int|null $insuranceNumber
 * @property string|null $primary_physician_id
 * @property-read int|null $age
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * @property Carbon|null $deleted_at
 */
#[Table(name: 'Patient')]
#[UseFactory(PatientFactory::class)]
class Patient extends Model
{
    use HasAutoIncrementingColumn, HasFactory, HasUlids, SoftDeletes;

    protected string $autoIncrementingColumn = 'patientID';

    protected $guarded = [];

    protected $appends = ['age'];

    protected function casts(): array
    {
        return [
            'dob' => 'date:Y-m-d',
        ];
    }

    protected function age(): Attribute
    {
        return Attribute::get(fn (): ?int => $this->dob === null
            ? null
            : (int) $this->dob->diffInYears(HospitalCalendar::today()));
    }

    public function primaryPhysician(): BelongsTo
    {
        return $this->belongsTo(Physician::class, 'primary_physician_id')->withoutGlobalScope(SoftDeletingScope::class);
    }
}
