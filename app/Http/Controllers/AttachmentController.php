<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Http\Requests\PresignAttachmentRequest;
use App\Services\AttachmentService;
use Illuminate\Http\JsonResponse;

class AttachmentController extends Controller
{
    public function __construct(private AttachmentService $attachmentService) {}

    public function presign(PresignAttachmentRequest $request): JsonResponse
    {
        $results = collect($request->files_meta)->map(
            fn($file) => $this->attachmentService->generatePresignedUrl($file['name'], $file['mime'])
        );

        return response()->json([
            'status' => true,
            'data'   => $results,
        ]);
    }
}
