<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\User;

use App\Http\Controllers\Controller;
use App\Http\Resources\User\WalletHistoryResource;
use App\Http\Resources\User\WalletResource;
use App\Models\Currency;
use App\Models\WalletTransactionStatus;
use App\Models\WalletTransactionType;
use App\Services\WalletService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class WalletController extends Controller
{
    public function __construct(
        protected WalletService $walletService
    ) {}

    /**
     * Display a listing of the user's wallets.
     */
    public function index(Request $request): JsonResponse
    {
        $user = $this->authenticatedUser($request);
        $wallets = $this->walletService
            ->getUserWallets(
                $user,
                $request->integer('per_page', 10)
            );

        return static::successResponse(
            WalletResource::collection($wallets),
            __('api.wallet.wallets_retrieved')
        );
    }

    /**
     * Top-up seed wallet for testing.
     */
    public function seed(Request $request): JsonResponse
    {
        $user = $this->authenticatedUser($request);
        $request->validate([
            'amount' => 'required|numeric|min:0.01',
            'currency' => 'required|string',
        ]);

        $currency = Currency::where('code', strtoupper($request->input('currency')))->firstOrFail();

        $wallet = $user->wallets()->firstOrCreate([
            'currency_id' => $currency->id,
        ], [
            'balance' => 0,
        ]);

        $wallet->balance += $request->input('amount');
        $wallet->save();

        $wallet->transactions()->create([
            'title' => 'Top Up (Test Seed)',
            'amount' => $request->input('amount'),
            'wallet_transaction_type_id' => WalletTransactionType::DEPOSIT_ID,
            'wallet_transaction_status_id' => WalletTransactionStatus::COMPLETED_ID,
        ]);

        return static::successResponse(
            new WalletResource($wallet->load('currency')),
            'Wallet seeded successfully'
        );
    }

    /**
     * Create a mock payment session for top-up.
     */
    public function createTopUpSession(Request $request): JsonResponse
    {
        $request->validate([
            'amount' => 'required|numeric|min:0.01',
            'currency' => 'required|string',
            'payment_method_type_code' => 'required|string',
        ]);

        $code = strtolower($request->input('payment_method_type_code'));

        $session = [
            'id' => 'ses_'.uniqid(),
            'status' => [
                'id' => 1,
                'name' => 'PENDING',
            ],
            'expires_at' => now()->addMinutes(15)->toIso8601String(),
        ];

        if (in_array($code, ['aba', 'acleda'])) {
            $session['redirect_url'] = 'https://example.com/payment?session='.$session['id'];
            $session['qr_payload'] = '00020101021229370016bakong@aba1234567890';
        }

        return static::successResponse(
            $session,
            'Top-up session created successfully'
        );
    }

    /**
     * Display the specified wallet.
     */
    public function show(string $id, Request $request): JsonResponse
    {
        $user = $this->authenticatedUser($request);
        $wallet = $user->wallets()->with('currency')->find((int) $id);

        if (! $wallet) {
            abort(404, __('api.wallet.not_found'));
        }

        return static::successResponse(
            new WalletResource($wallet),
            __('api.wallet.retrieved')
        );
    }

    /**
     * Display the specified wallet history.
     */
    public function history(string $id, Request $request): JsonResponse
    {
        $user = $this->authenticatedUser($request);
        $wallet = $user->wallets()->with('currency')->find((int) $id);

        if (! $wallet) {
            abort(404, __('api.wallet.not_found'));
        }

        $histories = $this->walletService
            ->getWalletHistory(
                $wallet,
                $request->integer('per_page', 10)
            );

        return static::successResponse(
            WalletHistoryResource::collection($histories),
            __('api.wallet.history_retrieved')
        );
    }
}
