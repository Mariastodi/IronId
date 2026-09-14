<?php

namespace Tests\Feature;

use App\Enums\MembershipStatus;
use App\Models\Member;
use App\Services\MembershipService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Carbon;
use Tests\TestCase;

class MembershipServiceTest extends TestCase
{
    use RefreshDatabase;

    public function test_active_plan_returns_active_status(): void
    {
        $member = Member::factory()->create([
            'plan_expires_at' => Carbon::today()->addDays(20),
        ]);

        $service = app(MembershipService::class);

        $this->assertEquals(MembershipStatus::Active, $service->statusFor($member));
    }

    public function test_plan_expiring_soon_is_flagged(): void
    {
        $member = Member::factory()->create([
            'plan_expires_at' => Carbon::today()->addDays(3),
        ]);

        $service = app(MembershipService::class);

        $this->assertEquals(MembershipStatus::ExpiringSoon, $service->statusFor($member));
    }

    public function test_expired_plan_is_flagged(): void
    {
        $member = Member::factory()->create([
            'plan_expires_at' => Carbon::today()->subDays(2),
        ]);

        $service = app(MembershipService::class);

        $this->assertEquals(MembershipStatus::Expired, $service->statusFor($member));
    }

    public function test_renew_extends_from_current_expiration_when_still_valid(): void
    {
        $member = Member::factory()->create([
            'plan_expires_at' => Carbon::today()->addDays(10),
        ]);
        $member->plan->update(['duration_days' => 30]);

        $service = app(MembershipService::class);
        $renewed = $service->renew($member->fresh('plan'));

        $this->assertEquals(
            Carbon::today()->addDays(40)->toDateString(),
            $renewed->plan_expires_at->toDateString()
        );
    }
}
