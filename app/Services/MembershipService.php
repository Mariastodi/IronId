<?php

namespace App\Services;

use App\Enums\MembershipStatus;
use App\Models\Member;
use Carbon\Carbon;

class MembershipService
{
    private const EXPIRING_SOON_DAYS = 5;

    public function statusFor(Member $member): MembershipStatus
    {
        if (! $member->active || ! $member->plan_expires_at) {
            return MembershipStatus::Inactive;
        }

        $daysRemaining = $this->daysRemaining($member);

        if ($daysRemaining < 0) {
            return MembershipStatus::Expired;
        }

        if ($daysRemaining <= self::EXPIRING_SOON_DAYS) {
            return MembershipStatus::ExpiringSoon;
        }

        return MembershipStatus::Active;
    }

    public function daysRemaining(Member $member): ?int
    {
        if (! $member->plan_expires_at) {
            return null;
        }

        return (int) Carbon::today()->diffInDays($member->plan_expires_at, false);
    }

    public function renew(Member $member): Member
    {
        $duration = $member->plan?->duration_days ?? 30;
        $base = $member->plan_expires_at && $member->plan_expires_at->isFuture()
            ? $member->plan_expires_at
            : Carbon::today();

        $member->update([
            'plan_started_at' => $member->plan_started_at ?? Carbon::today(),
            'plan_expires_at' => $base->copy()->addDays($duration),
        ]);

        return $member->fresh();
    }
}
