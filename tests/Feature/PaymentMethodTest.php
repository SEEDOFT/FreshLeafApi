<?php

namespace Tests\Feature;

use App\Models\PaymentMethodStatus;
use App\Models\PaymentMethodType;
use App\Models\User;
use App\Models\UserPaymentMethod;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PaymentMethodTest extends TestCase
{
    use RefreshDatabase;

    private User $user;

    private PaymentMethodType $paymentMethodType;

    private PaymentMethodStatus $paymentMethodStatus;

    protected function setUp(): void
    {
        parent::setUp();

        $this->user = User::factory()->create();
        $this->paymentMethodType = PaymentMethodType::query()->findOrFail(PaymentMethodType::VISA);
        $this->paymentMethodStatus = PaymentMethodStatus::query()->findOrFail(PaymentMethodStatus::ACTIVE);
    }

    public function test_user_can_list_their_payment_methods(): void
    {
        UserPaymentMethod::factory()->count(3)->create(['user_id' => $this->user->id]);
        UserPaymentMethod::factory()->count(2)->create();

        $response = $this->actingAs($this->user)
            ->getJson('/api/v1/users/payment-methods');

        $response->assertStatus(200)
            ->assertJsonCount(3, 'data');
    }

    public function test_user_can_create_payment_method(): void
    {
        $data = [
            'payment_method_type_id' => $this->paymentMethodType->id,
            'payment_method_status_id' => $this->paymentMethodStatus->id,
            'label' => 'Personal Visa',
            'card_holder_name' => 'John Doe',
            'card_number' => '1234567890123456',
            'expiry_month' => 12,
            'expiry_year' => 2030,
            'cvv' => '123',
            'is_default' => true,
        ];

        $response = $this->actingAs($this->user)
            ->postJson('/api/v1/users/payment-methods', $data);

        $response->assertStatus(201)
            ->assertJsonPath('data.label', 'Personal Visa')
            ->assertJsonPath('data.card_number', '************3456')
            ->assertJsonPath('data.is_default', true);

        $this->assertDatabaseHas('user_payment_methods', [
            'user_id' => $this->user->id,
            'label' => 'Personal Visa',
            'is_default' => true,
        ]);

        $response2 = $this->actingAs($this->user)
            ->postJson('/api/v1/users/payment-methods', array_merge($data, ['label' => 'Second Visa']));

        $response2->assertStatus(201);
        $this->assertDatabaseHas('user_payment_methods', ['label' => 'Personal Visa', 'is_default' => false]);
        $this->assertDatabaseHas('user_payment_methods', ['label' => 'Second Visa', 'is_default' => true]);

        $paymentMethod = UserPaymentMethod::first();
        $this->assertEquals('1234567890123456', $paymentMethod->card_number);
        $this->assertEquals('123', $paymentMethod->cvv);
        $this->assertNotEquals('123', \DB::table('user_payment_methods')->first()->cvv);
    }

    public function test_user_can_show_their_payment_method(): void
    {
        $paymentMethod = UserPaymentMethod::factory()->create(['user_id' => $this->user->id]);

        $response = $this->actingAs($this->user)
            ->getJson("/api/v1/users/payment-methods/{$paymentMethod->id}");

        $response->assertStatus(200)
            ->assertJsonPath('data.id', $paymentMethod->id);
    }

    public function test_user_cannot_show_others_payment_method(): void
    {
        $otherUser = User::factory()->create();
        $paymentMethod = UserPaymentMethod::factory()->create(['user_id' => $otherUser->id]);

        $response = $this->actingAs($this->user)
            ->getJson("/api/v1/users/payment-methods/{$paymentMethod->id}");

        $response->assertStatus(404);
    }

    public function test_user_can_patch_update_their_payment_method(): void
    {
        $paymentMethod = UserPaymentMethod::factory()->create(['user_id' => $this->user->id, 'label' => 'Old Label']);

        $response = $this->actingAs($this->user)
            ->patchJson("/api/v1/users/payment-methods/{$paymentMethod->id}", [
                'label' => 'Updated via PATCH',
            ]);

        $response->assertStatus(200)
            ->assertJsonPath('data.label', 'Updated via PATCH');

        $this->assertDatabaseHas('user_payment_methods', [
            'id' => $paymentMethod->id,
            'label' => 'Updated via PATCH',
        ]);
    }

    public function test_user_can_put_replace_their_payment_method(): void
    {
        $paymentMethod = UserPaymentMethod::factory()->create(['user_id' => $this->user->id, 'label' => 'Old Label']);

        $data = [
            'payment_method_type_id' => $this->paymentMethodType->id,
            'payment_method_status_id' => $this->paymentMethodStatus->id,
            'label' => 'Full Replace via PUT',
            'card_holder_name' => 'Jane Doe',
            'card_number' => '9876543210987654',
            'expiry_month' => 5,
            'expiry_year' => 2028,
            'cvv' => '456',
            'is_default' => true,
        ];

        $response = $this->actingAs($this->user)
            ->putJson("/api/v1/users/payment-methods/{$paymentMethod->id}", $data);

        $response->assertStatus(200)
            ->assertJsonPath('data.label', 'Full Replace via PUT')
            ->assertJsonPath('data.card_holder_name', 'Jane Doe');

        $this->assertDatabaseHas('user_payment_methods', [
            'id' => $paymentMethod->id,
            'label' => 'Full Replace via PUT',
        ]);
    }

    public function test_user_can_delete_their_payment_method(): void
    {
        $paymentMethod = UserPaymentMethod::factory()->create(['user_id' => $this->user->id]);

        $response = $this->actingAs($this->user)
            ->deleteJson("/api/v1/users/payment-methods/{$paymentMethod->id}");

        $response->assertStatus(200);
        $this->assertDatabaseHas('user_payment_methods', [
            'id' => $paymentMethod->id,
            'payment_method_status_id' => PaymentMethodStatus::DELETE,
            'is_default' => false,
        ]);
    }
}
