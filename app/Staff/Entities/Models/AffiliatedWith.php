<?php

namespace App\Staff\Entities\Models;

use App\Hospital\Entities\Models\Department;
use Illuminate\Database\Eloquent\Attributes\Table;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\Pivot;
use Illuminate\Support\Carbon;

/**
 * @property string $physician_id
 * @property string $department_id
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 */
#[Table(name: 'AffiliatedWith')]
class AffiliatedWith extends Pivot
{
    protected $guarded = [];

    protected $foreignKey = 'physician_id';

    protected $relatedKey = 'department_id';

    public function physician(): BelongsTo
    {
        return $this->belongsTo(Physician::class, 'physician_id');
    }

    public function department(): BelongsTo
    {
        return $this->belongsTo(Department::class, 'department_id');
    }
}
