<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\EnrollFaceRequest;
use App\Http\Resources\MemberResource;
use App\Models\Member;
use App\Services\FaceRecognitionService;
use Illuminate\Validation\ValidationException;

class FaceEnrollmentController extends Controller
{
    public function __construct(
        private readonly FaceRecognitionService $faceRecognitionService,
    ) {}

    public function store(EnrollFaceRequest $request, Member $member)
    {
        $descriptor = $request->array('descriptor');

        if (! $this->faceRecognitionService->isValidDescriptor($descriptor)) {
            throw ValidationException::withMessages([
                'descriptor' => ['O descritor facial informado e invalido.'],
            ]);
        }

        $existingMatch = $this->faceRecognitionService->findBestMatch($descriptor);

        if ($existingMatch && $existingMatch->memberId !== $member->id) {
            throw ValidationException::withMessages([
                'descriptor' => ['Este rosto ja esta cadastrado para outro aluno.'],
            ]);
        }

        $member->update(['face_descriptor' => $descriptor]);

        return new MemberResource($member->load('plan'));
    }

    public function destroy(Member $member)
    {
        $member->update(['face_descriptor' => null]);

        return response()->json(null, 204);
    }
}
