<?php

namespace App\Staff\IO\Http\DTOs;

use Carbon\CarbonImmutable;

final readonly class OnCallData
{
    public function __construct(
        public ?string $nurseId,
        public ?CarbonImmutable $startDate,
        public ?CarbonImmutable $endDate,
    ) {}
}
