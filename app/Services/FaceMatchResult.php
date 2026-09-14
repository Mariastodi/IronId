<?php

namespace App\Services;

class FaceMatchResult
{
    public function __construct(
        public readonly int $memberId,
        public readonly float $distance,
    ) {}

    public function confidence(): float
    {
        return round(max(0, 1 - $this->distance), 4);
    }
}
