<?php

namespace App\Clinical\UseCases;

use App\Clinical\Entities\Models\Procedure;
use App\Clinical\Entities\ProcedureSortColumn;
use App\Clinical\IO\Http\DTOs\ProcedureSorting;
use App\Hospital\Entities\HospitalCalendar;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Query\JoinClause;
use Illuminate\Pagination\LengthAwarePaginator;

final class ListProcedures
{
    public function execute(int $perPage, int $page, ?string $search = null, ProcedureSorting $sorting = new ProcedureSorting): LengthAwarePaginator
    {
        $today = HospitalCalendar::today()->toDateString();

        return Procedure::query()
            ->leftJoin('Undergoes', fn (JoinClause $performedProcedures) => $performedProcedures
                ->on('Undergoes.procedure_id', '=', 'Procedure.id')
                ->whereNull('Undergoes.deleted_at')
                ->where('Undergoes.procDate', '<=', $today))
            ->when($search, fn (Builder $query) => $query->whereAny(['Procedure.name', 'Procedure.procID'], 'like', "%{$search}%"))
            ->groupBy('Procedure.id')
            ->select('Procedure.*')
            ->selectRaw('count(Undergoes.id) as performedCount')
            ->selectRaw('coalesce(round(100 * count(Undergoes.id) / nullif((select count(*) from Undergoes where deleted_at is null and procDate <= ?), 0), 2), 0) as performedShare', [$today])
            ->withCasts(['performedCount' => 'integer', 'performedShare' => 'float'])
            ->when(
                $sorting->column === ProcedureSortColumn::PerformedShare,
                fn (Builder $query) => $query->orderBy('performedCount', $sorting->direction->value)
                    ->orderBy('Procedure.name')
                    ->orderBy('Procedure.procID'),
                fn (Builder $query) => $query->orderBy('Procedure.name', $sorting->direction->value)
                    ->orderBy('Procedure.procID', $sorting->direction->value),
            )
            ->paginate($perPage, ['*'], 'page', $page);
    }
}
