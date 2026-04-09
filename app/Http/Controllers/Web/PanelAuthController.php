<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\UserStatus;
use App\Models\UserType;
use App\Models\VendorProfile;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\View\View;

class PanelAuthController extends Controller
{
    public function create(Request $request): View|RedirectResponse
    {
        if ($request->user() !== null) {
            return redirect()->intended('/');
        }

        return view('auth.login');
    }

    public function store(Request $request): RedirectResponse
    {
        $credentials = $request->validate([
            'email' => ['required', 'string', 'email'],
            'password' => ['required', 'string'],
        ]);

        if (! Auth::attempt($credentials, $request->boolean('remember'))) {
            return back()->withErrors([
                'email' => __('panels.auth.invalid_credentials'),
            ])->onlyInput('email');
        }

        $request->session()->regenerate();

        return redirect()->intended($this->resolveHomePath($request));
    }

    public function createRegister(): View
    {
        return view('auth.register');
    }

    public function storeRegister(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'first_name' => ['required', 'string', 'max:100'],
            'last_name' => ['required', 'string', 'max:100'],
            'email' => ['required', 'string', 'email', 'max:255', 'unique:users,email'],
            'phone_number' => ['required', 'string', 'max:40', 'unique:users,phone_number'],
            'password' => ['required', 'string', 'min:8', 'confirmed'],
            'business_name' => ['required', 'string', 'max:160'],
            'city' => ['required', 'string', 'max:120'],
            'province' => ['required', 'string', 'max:120'],
            'address' => ['required', 'string', 'max:255'],
        ]);

        DB::transaction(function () use ($validated): void {
            $user = User::query()->create([
                'first_name' => $validated['first_name'],
                'last_name' => $validated['last_name'],
                'email' => $validated['email'],
                'phone_number' => $validated['phone_number'],
                'password' => Hash::make($validated['password']),
                'user_type_id' => UserType::VENDOR,
                'user_status_id' => UserStatus::PENDING,
            ]);

            VendorProfile::query()->create([
                'user_id' => $user->id,
                'business_name' => $validated['business_name'],
                'contact_phone' => $validated['phone_number'],
                'city' => $validated['city'],
                'province' => $validated['province'],
                'address' => $validated['address'],
                'is_verified' => false,
            ]);
        });

        return redirect()->route('login')->with('status', __('panels.auth.registered_vendor_pending_notice'));
    }

    public function destroy(Request $request): RedirectResponse
    {
        Auth::guard('web')->logout();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('login');
    }

    private function resolveHomePath(Request $request): string
    {
        $user = $request->user();

        if ($user !== null && $user->isType(UserType::ADMIN) && (bool) $user->adminProfile?->super_admin) {
            return '/admin';
        }

        if ($user !== null && $user->isType(UserType::VENDOR)) {
            return '/vendor';
        }

        return '/';
    }
}
