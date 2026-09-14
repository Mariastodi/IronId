<?php

namespace Tests\Feature;

use App\Models\Member;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class FaceEnrollmentTest extends TestCase
{
    use RefreshDatabase;

    public function test_staff_can_enroll_a_member_face(): void
    {
        $staff = User::factory()->admin()->create();
        $member = Member::factory()->create(['face_descriptor' => null]);

        $descriptor = array_fill(0, 128, 0.42);

        $response = $this->actingAs($staff, 'sanctum')
            ->postJson("/api/members/{$member->id}/face", ['descriptor' => $descriptor]);

        $response->assertOk()->assertJsonPath('data.face_enrolled', true);
    }

    public function test_it_rejects_a_descriptor_with_wrong_length(): void
    {
        $staff = User::factory()->admin()->create();
        $member = Member::factory()->create();

        $response = $this->actingAs($staff, 'sanctum')
            ->postJson("/api/members/{$member->id}/face", ['descriptor' => [0.1, 0.2]]);

        $response->assertUnprocessable();
    }

    public function test_recognize_endpoint_returns_the_matching_member(): void
    {
        $staff = User::factory()->admin()->create();
        $descriptor = array_fill(0, 128, 0.3);
        $member = Member::factory()->create(['face_descriptor' => $descriptor, 'plan_expires_at' => now()->addMonth()]);

        $response = $this->actingAs($staff, 'sanctum')
            ->postJson('/api/kiosk/recognize', ['descriptor' => $descriptor]);

        $response->assertOk()->assertJsonPath('data.member.id', $member->id);

        $this->assertDatabaseHas('check_ins', ['member_id' => $member->id]);
    }

    public function test_recognize_endpoint_returns_404_for_unknown_face(): void
    {
        $staff = User::factory()->admin()->create();
        Member::factory()->create(['face_descriptor' => array_fill(0, 128, 5.0)]);

        $response = $this->actingAs($staff, 'sanctum')
            ->postJson('/api/kiosk/recognize', ['descriptor' => array_fill(0, 128, 0.0)]);

        $response->assertNotFound();
    }
}
