<?php

namespace App\Patient\Entities\Models;

use App\Staff\Entities\Models\Physician;
use Illuminate\Database\Eloquent\Attributes\Table;
use Illuminate\Database\Eloquent\Concerns\HasUlids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Carbon;

/**
 * @property string $id
 * @property int|null $ssn
 * @property string $name
 * @property string|null $address
 * @property Carbon|null $dob
 * @property string|null $phone
 * @property int|null $insuranceNumber
 * @property string|null $primary_physician_id
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * @property Carbon|null $deleted_at
 */
#[Table(name: 'Patient')]
class Patient extends Model
{
    use HasUlids, SoftDeletes;

    protected $guarded = [];

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'dob' => 'date',
        ];
    }

    /**
     * @return BelongsTo<Physician, $this>
     */
    public function primaryPhysician(): BelongsTo
    {
        return $this->belongsTo(Physician::class, 'primary_physician_id');
    }
}
