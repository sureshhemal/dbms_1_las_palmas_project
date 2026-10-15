<?php

namespace App\Hospital\IO\Http\DTOs;

final readonly class DepartmentData
{
    public function __construct(
        public ?int $deptID,
        public ?string $headId,
    ) {}
}
