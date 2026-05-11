<?php

declare(strict_types=1);

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\UserType;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Symfony\Component\HttpFoundation\StreamedResponse;

class VerificationDocumentController extends Controller
{
    /**
     * Serve a private verification document to an authorized admin.
     */
    public function show(Request $request, string $path): JsonResponse|StreamedResponse
    {
        $user = $this->authenticatedUser($request, UserType::ADMIN);

        if (str_contains($path, '..')) {
            return static::errorResponse('Document not found.', 404);
        }

        if (! Storage::disk('local')->exists($path)) {
            return static::errorResponse('Document not found.', 404);
        }

        return Storage::disk('local')->response($path);
    }
}
