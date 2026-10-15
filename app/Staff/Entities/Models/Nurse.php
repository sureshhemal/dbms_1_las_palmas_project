<?php

namespace App\Staff\Entities\Models;

use App\Foundation\Entities\Concerns\HasAutoIncrementingColumn;
use App\Staff\Entities\NursePosition;
use Database\Factories\Staff\NurseFactory;
use Illuminate\Database\Eloquent\Attributes\Table;
use Illuminate\Database\Eloquent\Attributes\UseFactory;
use Illuminate\Database\Eloquent\Concerns\HasUlids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Carbon;

/**
 * @property string $id
 * @property int $nurseID
 * @property string $name
 * @property NursePosition $position
 * @property string|null $ssn
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * @property Carbon|null $deleted_at
 */
#[Table(name: 'Nurse')]
#[UseFactory(NurseFactory::class)]
class Nurse extends Model
{
    use HasAutoIncrementingColumn, HasFactory, HasUlids, SoftDeletes;

    protected string $autoIncrementingColumn = 'nurseID';

    protected $guarded = [];

    protected function casts(): array
    {
        return [
            'position' => NursePosition::class,
        ];
    }

    public function onCalls(): HasMany
    {
        return $this->hasMany(OnCall::class);
    }
}
