<?php

namespace App\Staff\Entities\Models;

use App\Foundation\Entities\Concerns\HasAutoIncrementingColumn;
use App\Hospital\Entities\Models\Department;
use App\Staff\Entities\PhysicianPosition;
use App\Staff\IO\Database\QueryBuilders\PhysiciansQueryBuilder;
use Database\Factories\Staff\PhysicianFactory;
use Illuminate\Database\Eloquent\Attributes\Table;
use Illuminate\Database\Eloquent\Attributes\UseFactory;
use Illuminate\Database\Eloquent\Concerns\HasUlids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
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
#[UseFactory(PhysicianFactory::class)]
class Physician extends Model
{
    use HasAutoIncrementingColumn, HasFactory, HasUlids, SoftDeletes;

    protected string $autoIncrementingColumn = 'physicianID';

    protected $guarded = [];

    public static function query(): PhysiciansQueryBuilder
    {
        return (new self)->newQuery();
    }

    public function newEloquentBuilder($query): PhysiciansQueryBuilder
    {
        return new PhysiciansQueryBuilder($query);
    }

    protected function casts(): array
    {
        return [
            'position' => PhysicianPosition::class,
        ];
    }

    public function departments(): BelongsToMany
    {
        return $this->belongsToMany(Department::class, 'AffiliatedWith', 'physician_id', 'department_id')
            ->using(AffiliatedWith::class)
            ->withTimestamps();
    }

    public function headedDepartments(): HasMany
    {
        return $this->hasMany(Department::class, 'head_id');
    }
}
