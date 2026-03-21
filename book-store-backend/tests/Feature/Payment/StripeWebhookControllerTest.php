<?php

declare(strict_types=1);

namespace Tests\Feature\Payment;

use App\Application\Cart\Interfaces\PaymentGatewayInterface;
use App\Domain\Access\Interfaces\UserBookAccessRepositoryInterface;
use App\Domain\Access\Interfaces\UserSubscriptionRepositoryInterface;
use App\Infrastructure\Persistence\Models\BookModel;
use App\Infrastructure\Persistence\Models\UserModel;
use Illuminate\Contracts\Container\BindingResolutionException;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\Fakes\FakePaymentGateway;
use Tests\TestCase;

final class StripeWebhookControllerTest extends TestCase
{
    use DatabaseTransactions;

    private FakePaymentGateway $gateway;

    protected function setUp(): void
    {
        parent::setUp();

        $this->gateway = new FakePaymentGateway();
        $this->instance(PaymentGatewayInterface::class, $this->gateway);
    }

    // ── Signature verification ─────────────────────────────────────────────

    public function test_returns_400_when_signature_invalid(): void
    {
        $this->gateway->failNextWebhook();

        $this->postJson(route('webhooks.stripe'), [], ['Stripe-Signature' => 'bad'])
            ->assertStatus(400)
            ->assertJsonFragment(['message' => 'Invalid signature.']);
    }

    // ── checkout.session.completed ─────────────────────────────────────────

    /**
     * @throws BindingResolutionException
     */
    public function test_grants_book_access_on_checkout_completed(): void
    {
        /** @var UserModel $user */
        $user = UserModel::factory()->create();
        /** @var BookModel $book */
        $book = BookModel::factory()->create();

        $this->gateway->pushWebhookEvent(
            FakePaymentGateway::makeCheckoutEvent(
                userId: $user->id,
                cartId: 1,
                bookIds: [$book->id],
            ),
        );

        $this->postJson(route('webhooks.stripe'))
            ->assertOk()
            ->assertExactJson(['received' => true]);

        $this->assertTrue(
            $this->app->make(UserBookAccessRepositoryInterface::class)
                ->hasAccess($user->id, $book->id),
        );
    }

    /**
     * @throws BindingResolutionException
     */
    public function test_grants_access_to_multiple_books(): void
    {
        /** @var UserModel $user */
        $user = UserModel::factory()->create();
        /** @var BookModel $bookA */
        $bookA = BookModel::factory()->create();
        /** @var BookModel $bookB */
        $bookB = BookModel::factory()->create();

        $this->gateway->pushWebhookEvent(
            FakePaymentGateway::makeCheckoutEvent(
                userId: $user->id,
                cartId: 2,
                bookIds: [$bookA->id, $bookB->id],
            ),
        );

        $this->postJson(route('webhooks.stripe'))->assertOk();

        $repo = $this->app->make(UserBookAccessRepositoryInterface::class);

        $this->assertTrue($repo->hasAccess($user->id, $bookA->id));
        $this->assertTrue($repo->hasAccess($user->id, $bookB->id));
    }

    public function test_checkout_is_idempotent(): void
    {
        /** @var UserModel $user */
        $user = UserModel::factory()->create();
        /** @var BookModel $book */
        $book = BookModel::factory()->create();

        $event = FakePaymentGateway::makeCheckoutEvent(
            userId: $user->id,
            cartId: 3,
            bookIds: [$book->id],
        );

        $this->gateway->pushWebhookEvent($event);
        $this->postJson(route('webhooks.stripe'))->assertOk();

        $this->gateway->pushWebhookEvent($event);
        $this->postJson(route('webhooks.stripe'))->assertOk();

        $this->assertDatabaseCount('user_book_access', 1);
    }

    public function test_checkout_without_book_ids_does_not_grant_access(): void
    {
        /** @var UserModel $user */
        $user = UserModel::factory()->create();

        $this->gateway->pushWebhookEvent(
            FakePaymentGateway::makeCheckoutEvent(
                userId: $user->id,
                cartId: 4,
                bookIds: [],
            ),
        );

        $this->postJson(route('webhooks.stripe'))->assertOk();

        $this->assertDatabaseEmpty('user_book_access');
    }

    public function test_checkout_ignores_missing_metadata(): void
    {
        $event = (object) [
            'type' => 'checkout.session.completed',
            'data' => (object) [
                'object' => (object) [
                    'id'       => 'cs_test_no_meta',
                    'metadata' => null,
                ],
            ],
        ];

        $this->gateway->pushWebhookEvent($event);

        $this->postJson(route('webhooks.stripe'))->assertOk();

        $this->assertDatabaseEmpty('user_book_access');
    }

    // ── customer.subscription.* ───────────────────────────────────────────

    /**
     * @throws BindingResolutionException
     */
    public function test_grants_subscription_on_subscription_created(): void
    {
        /** @var UserModel $user */
        $user = UserModel::factory()->create();

        $expiresAt = now()->addMonth()->timestamp;

        $this->gateway->pushWebhookEvent(
            FakePaymentGateway::makeSubscriptionEvent(
                userId: $user->id,
                stripeSubscriptionId: 'sub_test_123',
                currentPeriodEnd: $expiresAt,
            ),
        );

        $this->postJson(route('webhooks.stripe'))->assertOk();

        $subscription = $this->app->make(UserSubscriptionRepositoryInterface::class)
            ->findActiveByUser($user->id);

        $this->assertNotNull($subscription);
        $this->assertSame('sub_test_123', $subscription->stripeSubscriptionId);
    }

    public function test_subscription_grant_is_idempotent(): void
    {
        /** @var UserModel $user */
        $user = UserModel::factory()->create();

        $event = FakePaymentGateway::makeSubscriptionEvent(
            userId: $user->id,
            stripeSubscriptionId: 'sub_idem',
            currentPeriodEnd: now()->addMonth()->timestamp,
        );

        $this->gateway->pushWebhookEvent($event);
        $this->postJson(route('webhooks.stripe'))->assertOk();

        $this->gateway->pushWebhookEvent($event);
        $this->postJson(route('webhooks.stripe'))->assertOk();

        $this->assertDatabaseCount('user_subscriptions', 1);
    }

    public function test_subscription_handles_updated_event(): void
    {
        /** @var UserModel $user */
        $user = UserModel::factory()->create();

        $this->gateway->pushWebhookEvent(
            FakePaymentGateway::makeSubscriptionEvent(
                userId: $user->id,
                stripeSubscriptionId: 'sub_upd',
                currentPeriodEnd: now()->addMonth()->timestamp,
                type: 'customer.subscription.updated',
            ),
        );

        $this->postJson(route('webhooks.stripe'))->assertOk();

        $this->assertDatabaseHas('user_subscriptions', [
            'user_id'                => $user->id,
            'stripe_subscription_id' => 'sub_upd',
        ]);
    }

    public function test_unknown_event_type_returns_200(): void
    {
        $this->gateway->pushWebhookEvent((object) [
            'type' => 'payment_intent.created',
            'data' => (object) ['object' => (object) []],
        ]);

        $this->postJson(route('webhooks.stripe'))->assertOk();
    }
}
