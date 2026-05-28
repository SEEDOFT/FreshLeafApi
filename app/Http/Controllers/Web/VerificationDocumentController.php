<?php

declare(strict_types=1);

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\User;
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
        /** @var User|null $user */
        $user = $request->user();

        if (! $user) {
            return static::errorResponse(__('api.general.unauthenticated'), 401);
        }

        $isAdmin = $user->user_type_id === UserType::ADMIN_ID;
        $isVendor = $user->user_type_id === UserType::VENDOR_ID;

        if (! $isAdmin && ! $isVendor) {
            return static::errorResponse(__('api.general.unauthorized'), 403);
        }

        if (str_contains($path, '..')) {
            return static::errorResponse(__('api.general.not_found'), 404);
        }

        if (! Storage::disk('local')->exists($path)) {
            return static::errorResponse(__('api.general.not_found'), 404);
        }

        if ($isVendor) {
            $profile = $user->vendorProfile;
            $financial = $user->vendorFinancialDetails;

            $ownedFiles = array_filter([
                $profile->id_card_front,
                $profile->id_card_back,
                $profile->store_front_image,
                $profile->organic_certificate_url,
                $financial->qr_code,
            ]);

            if (! in_array(basename($path), $ownedFiles, true)) {
                return static::errorResponse(__('api.general.document_unauthorized'), 403);
            }
        }

        return Storage::disk('local')->response($path);
    }
}
