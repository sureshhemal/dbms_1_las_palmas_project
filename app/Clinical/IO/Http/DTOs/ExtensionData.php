<?php

namespace App\Clinical\IO\Http\DTOs;

use App\Clinical\Entities\Models\Stay;
use Carbon\CarbonImmutable;

final readonly class ExtensionData
{
    public function __construct(
        public Stay $stay,
        public CarbonImmutable $endDate,
    ) {}
}
