<?php

namespace App\Services;

use App\Models\Member;
use Illuminate\Support\Collection;

class FaceRecognitionService
{
    private const DEFAULT_MATCH_THRESHOLD = 0.5;

    private const DESCRIPTOR_LENGTH = 128;

    public function euclideanDistance(array $a, array $b): float
    {
        $sum = 0.0;

        foreach ($a as $index => $value) {
            $diff = $value - ($b[$index] ?? 0.0);
            $sum += $diff * $diff;
        }

        return sqrt($sum);
    }

    public function isValidDescriptor(array $descriptor): bool
    {
        if (count($descriptor) !== self::DESCRIPTOR_LENGTH) {
            return false;
        }

        foreach ($descriptor as $value) {
            if (! is_numeric($value) || ! is_finite((float) $value)) {
                return false;
            }
        }

        return true;
    }

    public function findBestMatch(array $descriptor, float $threshold = self::DEFAULT_MATCH_THRESHOLD): ?FaceMatchResult
    {
        $candidates = Member::query()
            ->where('active', true)
            ->whereNotNull('face_descriptor')
            ->get(['id', 'face_descriptor']);

        $best = $this->rankCandidates($candidates, $descriptor)->first();

        if (! $best || $best->distance > $threshold) {
            return null;
        }

        return $best;
    }

    private function rankCandidates(Collection $candidates, array $descriptor): Collection
    {
        return $candidates
            ->map(function (Member $candidate) use ($descriptor) {
                $distance = $this->euclideanDistance($descriptor, $candidate->face_descriptor);

                return new FaceMatchResult($candidate->id, $distance);
            })
            ->sortBy('distance')
            ->values();
    }
}
