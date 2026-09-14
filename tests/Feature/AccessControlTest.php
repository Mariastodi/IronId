<?php

namespace Tests\Feature;

use App\Models\Member;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AccessControlTest extends TestCase
{
    use RefreshDatabase;

    public function test_private_endpoints_require_authentication(): void
    {
        $this->getJson('/api/members')->assertUnauthorized();
        $this->postJson('/api/kiosk/recognize')->assertUnauthorized();
    }

    public function test_expired_plan_cannot_check_in_manually_or_by_face(): void
    {
        $member = Member::factory()->create(['plan_expires_at' => now()->subDay(), 'face_descriptor' => array_fill(0, 128, 0.3)]);
        $this->actingAs(User::factory()->admin()->create(), 'sanctum');
        $this->postJson('/api/check-ins', ['member_id' => $member->id])->assertUnprocessable();
        $this->postJson('/api/kiosk/recognize', ['descriptor' => array_fill(0, 128, 0.3)])->assertUnprocessable();
        $this->assertDatabaseCount('check_ins', 0);
    }

    public function test_repeated_face_reads_return_same_check_in_and_name_without_descriptor(): void
    {
        $member = Member::factory()->create(['plan_expires_at' => now()->addMonth(), 'face_descriptor' => array_fill(0, 128, 0.3)]);
        $this->actingAs(User::factory()->admin()->create(), 'sanctum');
        for ($i = 0; $i < 2; $i++) {
            $this->postJson('/api/kiosk/recognize', ['descriptor' => array_fill(0, 128, 0.3)])
                ->assertOk()->assertJsonPath('data.member.name', $member->name)->assertJsonMissingPath('data.member.face_descriptor');
        }
        $this->assertDatabaseCount('check_ins', 1);
        $this->travel(61)->seconds();
        $this->postJson('/api/check-ins', ['member_id' => $member->id])->assertSuccessful();
        $this->assertDatabaseCount('check_ins', 2);
    }

    public function test_public_pages_do_not_embed_kiosk_secret(): void
    {
        config(['services.kiosk.token' => 'secret-not-for-html']);
        $this->get('/kiosk')->assertOk()->assertDontSee('secret-not-for-html');
        $this->get('/')->assertOk()->assertSee('Gestão');
    }

    public function test_dashboard_returns_global_counts_and_invalid_dates_are_rejected(): void
    {
        Member::factory()->create(['face_descriptor' => array_fill(0, 128, 0.3)]);
        Member::factory()->create();
        $this->actingAs(User::factory()->admin()->create(), 'sanctum');
        $this->getJson('/api/dashboard')->assertOk()
            ->assertJsonPath('data.members_count', 2)
            ->assertJsonPath('data.enrolled_members_count', 1)
            ->assertJsonPath('data.today_check_ins_count', 0);
        $this->getJson('/api/check-ins?date=invalid')->assertUnprocessable();
    }
}
