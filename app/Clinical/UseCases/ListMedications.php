<?php

namespace App\Clinical\UseCases;

use App\Clinical\Entities\MedicationSortColumn;
use App\Clinical\Entities\Models\Medication;
use App\Clinical\IO\Http\DTOs\MedicationSorting;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Query\JoinClause;
use Illuminate\Pagination\LengthAwarePaginator;

final class ListMedications
{
    public function execute(int $perPage, int $page, ?string $search = null, MedicationSorting $sorting = new MedicationSorting): LengthAwarePaginator
    {
        return Medication::query()
            ->leftJoin('Prescribes', fn (JoinClause $prescriptions) => $prescriptions
                ->on('Prescribes.medication_id', '=', 'Medication.id')
                ->whereNull('Prescribes.deleted_at'))
            ->when($search, fn (Builder $query) => $query->whereAny(['Medication.name', 'Medication.medID'], 'like', "%{$search}%"))
            ->groupBy('Medication.id')
            ->select('Medication.*')
            ->selectRaw('count(Prescribes.id) as prescriptionsCount')
            ->selectRaw('coalesce(round(100 * count(Prescribes.id) / nullif((select count(*) from Prescribes where deleted_at is null), 0), 2), 0) as prescriptionShare')
            ->withCasts(['prescriptionsCount' => 'integer', 'prescriptionShare' => 'float'])
            ->when(
                $sorting->column === MedicationSortColumn::PrescriptionShare,
                fn (Builder $query) => $query->orderBy('prescriptionsCount', $sorting->direction->value)
                    ->orderBy('Medication.name')
                    ->orderBy('Medication.medID'),
                fn (Builder $query) => $query->orderBy('Medication.name', $sorting->direction->value)
                    ->orderBy('Medication.medID', $sorting->direction->value),
            )
            ->paginate($perPage, ['*'], 'page', $page);
    }
}
