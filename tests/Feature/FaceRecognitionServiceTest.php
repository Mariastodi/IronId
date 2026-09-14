<?php

namespace Tests\Feature;

use App\Models\Member;
use App\Services\FaceRecognitionService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class FaceRecognitionServiceTest extends TestCase
{
    use RefreshDatabase;

    public function test_it_calculates_euclidean_distance_correctly(): void
    {
        $service = app(FaceRecognitionService::class);

        $a = array_fill(0, 128, 0.0);
        $b = array_fill(0, 128, 0.0);
        $b[0] = 3.0;
        $b[1] = 4.0;

        $this->assertEquals(5.0, $service->euclideanDistance($a, $b));
    }

    public function test_it_validates_descriptor_length(): void
    {
        $service = app(FaceRecognitionService::class);

        $this->assertTrue($service->isValidDescriptor(array_fill(0, 128, 0.1)));
        $this->assertFalse($service->isValidDescriptor(array_fill(0, 64, 0.1)));
    }

    public function test_it_finds_the_closest_matching_member(): void
    {
        $service = app(FaceRecognitionService::class);

        $baseDescriptor = array_fill(0, 128, 0.5);

        $closeMatch = Member::factory()->create(['face_descriptor' => $baseDescriptor]);

        $farDescriptor = array_fill(0, 128, 5.0);
        Member::factory()->create(['face_descriptor' => $farDescriptor]);

        $queryDescriptor = array_fill(0, 128, 0.51);

        $result = $service->findBestMatch($queryDescriptor, threshold: 1.0);

        $this->assertNotNull($result);
        $this->assertEquals($closeMatch->id, $result->memberId);
    }

    public function test_it_returns_null_when_no_member_is_close_enough(): void
    {
        $service = app(FaceRecognitionService::class);

        Member::factory()->create(['face_descriptor' => array_fill(0, 128, 5.0)]);

        $queryDescriptor = array_fill(0, 128, 0.0);

        $result = $service->findBestMatch($queryDescriptor, threshold: 0.5);

        $this->assertNull($result);
    }
}
