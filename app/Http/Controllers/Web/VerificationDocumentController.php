<?php

declare(strict_types=1);

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\UserType;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Symfony\Component\HttpFoundation\StreamedResponse;

class VerificationDocumentController extends Controller
{
    /**
     * Serve a private verification document to an authorized admin.
     */
    public function show(Request $request, string $path): StreamedResponse
    {
        $user = $request->user();

        // 1. Authorize only admins
        if (! $user || $user->user_type_id !== UserType::ADMIN) {
            abort(403, 'Unauthorized access to sensitive documents.');
        }

        // 2. Validate path (security check to prevent traversal)
        if (str_contains($path, '..')) {
            abort(404);
        }

        // 3. Serve file from private 'local' disk
        if (! Storage::disk('local')->exists($path)) {
            abort(404);
        }

        return Storage::disk('local')->response($path);
    }
}
