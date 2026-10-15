<?php

namespace App\Hospital\Entities\Models;

use App\Hospital\Entities\DepartmentName;
use App\Staff\Entities\Models\AffiliatedWith;
use App\Staff\Entities\Models\Physician;
use Illuminate\Database\Eloquent\Attributes\Table;
use Illuminate\Database\Eloquent\Concerns\HasUlids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Carbon;

/**
 * @property string $id
 * @property int $deptID
 * @property DepartmentName $name
 * @property string|null $head_id
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * @property Carbon|null $deleted_at
 */
#[Table(name: 'Department')]
class Department extends Model
{
    use HasUlids, SoftDeletes;

    protected $guarded = [];

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'name' => DepartmentName::class,
        ];
    }

    /**
     * @return BelongsTo<Physician, $this>
     */
    public function head(): BelongsTo
    {
        return $this->belongsTo(Physician::class, 'head_id');
    }

    /**
     * @return BelongsToMany<Physician, $this, AffiliatedWith>
     */
    public function physicians(): BelongsToMany
    {
        return $this->belongsToMany(Physician::class, 'AffiliatedWith', 'department_id', 'physician_id')
            ->using(AffiliatedWith::class)
            ->withTimestamps()
            ->wherePivotNull('deleted_at');
    }
}
