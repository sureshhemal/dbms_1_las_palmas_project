<?php

namespace App\Staff\Entities\Models;

use App\Hospital\Entities\Models\Department;
use App\Staff\Entities\PhysicianPosition;
use Illuminate\Database\Eloquent\Attributes\Table;
use Illuminate\Database\Eloquent\Concerns\HasUlids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Carbon;

/**
 * @property string $id
 * @property int $physicianID
 * @property string $name
 * @property PhysicianPosition $position
 * @property string|null $ssn
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * @property Carbon|null $deleted_at
 */
#[Table(name: 'Physician')]
class Physician extends Model
{
    use HasUlids, SoftDeletes;

    protected $guarded = [];

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'position' => PhysicianPosition::class,
        ];
    }

    /**
     * @return BelongsToMany<Department, $this, AffiliatedWith>
     */
    public function departments(): BelongsToMany
    {
        return $this->belongsToMany(Department::class, 'AffiliatedWith', 'physician_id', 'department_id')
            ->using(AffiliatedWith::class)
            ->withTimestamps()
            ->wherePivotNull('deleted_at');
    }
}
