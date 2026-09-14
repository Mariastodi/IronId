<?php

namespace App\Http\Controllers\Api;

use App\Enums\CheckInMethod;
use App\Http\Controllers\Controller;
use App\Http\Requests\RecognizeFaceRequest;
use App\Http\Resources\RecognitionResultResource;
use App\Models\Member;
use App\Services\CheckInService;
use App\Services\FaceRecognitionService;

class FaceRecognitionController extends Controller
{
    public function __construct(
        private readonly FaceRecognitionService $faceRecognitionService,
    ) {}

    public function recognize(RecognizeFaceRequest $request)
    {
        $descriptor = $request->array('descriptor');

        $match = $this->faceRecognitionService->findBestMatch($descriptor);

        if (! $match) {
            return response()->json([
                'recognized' => false,
                'message' => 'Nenhum aluno reconhecido. Tente novamente ou faca o check-in manual.',
            ], 404);
        }

        $member = Member::with('plan')->findOrFail($match->memberId);

        $checkIn = app(CheckInService::class)->record($member, CheckInMethod::Face, $match->distance);

        return new RecognitionResultResource($member, $match->confidence(), $checkIn);
    }
}
