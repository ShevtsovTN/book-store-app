<?php

declare(strict_types=1);

namespace Tests\Fakes;

use App\Application\Cart\Interfaces\PaymentGatewayInterface;
use App\Domain\Shared\ValueObjects\Money;
use PHPUnit\Framework\Assert;
use Stripe\Exception\SignatureVerificationException;
use Symfony\Component\HttpFoundation\Response;

final class FakePaymentGateway implements PaymentGatewayInterface
{
    private ?int   $lastCartId = null;

    private ?Money $lastAmount = null;

    /** @var list<object> */
    private array $webhookEvents = [];

    private bool $throwOnWebhook = false;

    public function createSession(int $cartId, Money $amount, array $metadata = []): string
    {
        $this->lastCartId = $cartId;
        $this->lastAmount = $amount;

        return "https://fake-payment.test/session/{$cartId}";
    }

    /**
     * @throws SignatureVerificationException
     */
    public function constructWebhookEvent(string $payload, string $signature): object
    {
        if ($this->throwOnWebhook) {
            throw new SignatureVerificationException('Invalid signature', Response::HTTP_BAD_REQUEST);
        }

        if (empty($this->webhookEvents)) {
            throw new \UnderflowException(
                'No webhook events queued. Call pushWebhookEvent() before the request.',
            );
        }

        return array_shift($this->webhookEvents);
    }

    public function pushWebhookEvent(object $event): void
    {
        $this->webhookEvents[] = $event;
    }

    public function failNextWebhook(): void
    {
        $this->throwOnWebhook = true;
    }

    public function reset(): void
    {
        $this->lastCartId     = null;
        $this->lastAmount     = null;
        $this->webhookEvents  = [];
        $this->throwOnWebhook = false;
    }

    public function assertSessionCreatedFor(int $cartId): void
    {
        Assert::assertSame($cartId, $this->lastCartId);
    }

    public function assertAmount(int $expectedAmount): void
    {
        Assert::assertSame($expectedAmount, $this->lastAmount?->amount);
    }

    /**
     * Builds a minimal checkout.session.completed event object.
     *
     * @param int[] $bookIds  List of book IDs to include as line items.
     */
    public static function makeCheckoutEvent(
        int   $userId,
        int   $cartId,
        array $bookIds = [],
        ?string $paymentIntentId = null,
    ): object {
        $lineItems = array_map(
            static fn(int $bookId) => (object) [
                'price' => (object) [
                    'product' => (object) [
                        'metadata' => (object) ['book_id' => (string) $bookId],
                    ],
                ],
            ],
            $bookIds,
        );

        return (object) [
            'type' => 'checkout.session.completed',
            'data' => (object) [
                'object' => (object) [
                    'id'             => 'cs_test_fake_' . $cartId,
                    'payment_intent' => $paymentIntentId ?? 'pi_fake_' . $cartId,
                    'metadata'       => (object) [
                        'user_id' => (string) $userId,
                        'cart_id' => (string) $cartId,
                    ],
                    'line_items' => (object) [
                        'data' => $lineItems,
                    ],
                ],
            ],
        ];
    }

    public static function makeSubscriptionEvent(
        int    $userId,
        string $stripeSubscriptionId,
        int    $currentPeriodEnd,
        string $type = 'customer.subscription.created',
    ): object {
        return (object) [
            'type' => $type,
            'data' => (object) [
                'object' => (object) [
                    'id'                  => $stripeSubscriptionId,
                    'current_period_end'  => $currentPeriodEnd,
                    'metadata'            => (object) [
                        'user_id' => (string) $userId,
                    ],
                ],
            ],
        ];
    }
}
