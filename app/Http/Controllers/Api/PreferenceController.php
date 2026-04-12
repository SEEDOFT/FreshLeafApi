<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\Preference\UpdatePanelPreferenceRequest;
use App\Http\Resources\PreferenceResource;
use App\Models\PanelPreference;
use Illuminate\Contracts\Auth\Authenticatable;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class PreferenceController extends Controller
{
    public function show(Request $request): JsonResponse
    {
        $preference = $this->resolvePreference($request->user());

        return $this->successResponse(new PreferenceResource($preference), 'Preferences loaded');
    }

    public function update(UpdatePanelPreferenceRequest $request): JsonResponse
    {
        $actor = $request->user();
        $validated = $request->validated();
        $preference = $this->resolvePreference($actor);

        $preference->update([
            'locale' => $validated['locale'] ?? $preference->locale,
            'theme' => $validated['theme'] ?? $preference->theme,
        ]);

        return $this->successResponse(new PreferenceResource($preference->fresh()), 'Preferences updated');
    }

    private function resolvePreference(?Authenticatable $actor): PanelPreference
    {
        if ($actor === null) {
            abort(401, 'Unauthenticated.');
        }

        /** @var PanelPreference $preference */
        $preference = PanelPreference::query()->firstOrCreate(
            [
                'account_type' => $actor::class,
                'account_id' => $actor->getAuthIdentifier(),
            ],
            [
                'locale' => 'km',
                'theme' => 'light',
            ]
        );

        return $preference;
    }
}
