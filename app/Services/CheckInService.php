<?php

namespace App\Services;

use App\Enums\CheckInMethod;
use App\Enums\MembershipStatus;
use App\Models\CheckIn;
use App\Models\Member;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class CheckInService
{
    public function record(Member $member, CheckInMethod $method, ?float $distance = null): CheckIn
    {
        return DB::transaction(function () use ($member, $method, $distance) {
            $member = Member::whereKey($member->id)->lockForUpdate()->firstOrFail();
            $status = app(MembershipService::class)->statusFor($member);
            if (! in_array($status, [MembershipStatus::Active, MembershipStatus::ExpiringSoon], true)) {
                throw ValidationException::withMessages(['membership' => ['Plano vencido ou inativo. Procure a recepção para regularizar o acesso.']]);
            }

            $recent = $member->checkIns()->where('checked_in_at', '>=', now()->subMinute())->latest('checked_in_at')->first();
            if ($recent) {
                return $recent;
            }

            return $member->checkIns()->create([
                'method' => $method,
                'match_distance' => $distance,
                'checked_in_at' => now(),
            ]);
        });
    }
}
