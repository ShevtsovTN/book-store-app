<?php

declare(strict_types=1);

namespace App\Presentation\Http\Controllers;

use App\Application\Access\UseCases\GrantBookAccess\GrantBookAccessCommand;
use App\Application\Access\UseCases\GrantBookAccess\GrantBookAccessHandler;
use App\Application\Access\UseCases\GrantSubscription\GrantSubscriptionCommand;
use App\Application\Access\UseCases\GrantSubscription\GrantSubscriptionHandler;
use App\Application\Cart\Interfaces\PaymentGatewayInterface;
use DateMalformedStringException;
use DateTimeImmutable;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Log;
use Stripe\Exception\SignatureVerificationException;
use Symfony\Component\HttpFoundation\Response;

final class StripeWebhookController extends Controller
{
    public function __construct(
        private readonly PaymentGatewayInterface  $gateway,
        private readonly GrantBookAccessHandler   $grantBookAccess,
        private readonly GrantSubscriptionHandler $grantSubscription,
    )
    {
    }

    /**
     * @throws DateMalformedStringException
     */
    public function __invoke(Request $request): JsonResponse
    {
        $signature = $request->header('Stripe-Signature', '');

        try {
            $event = $this->gateway->constructWebhookEvent(
                $request->getContent(),
                $signature,
            );
        } catch (SignatureVerificationException) {
            return new JsonResponse(['message' => 'Invalid signature.'], Response::HTTP_BAD_REQUEST);
        }

        match ($event->type) {
            'checkout.session.completed' => $this->handleCheckoutCompleted($event->data->object),
            'customer.subscription.created',
            'customer.subscription.updated' => $this->handleSubscriptionUpdate($event->data->object),
            default => null,
        };

        return new JsonResponse(['received' => true]);
    }

    private function handleCheckoutCompleted(object $session): void
    {
        $metadata = $session->metadata ?? null;

        if (null === $metadata) {
            return;
        }

        $userId = (int)($metadata->user_id ?? 0);
        $cartId = (int)($metadata->cart_id ?? 0);

        if (0 === $userId || 0 === $cartId) {
            Log::warning('Stripe webhook: missing metadata', [
                'session_id' => $session->id,
                'metadata' => (array)$metadata,
            ]);
            return;
        }

        $paymentIntentId = $session->payment_intent ?? null;

        if (!empty($session->line_items?->data)) {
            foreach ($session->line_items->data as $lineItem) {
                $bookId = (int)($lineItem->price?->product?->metadata?->book_id ?? 0);

                if ($bookId > 0) {
                    $this->grantBookAccess->handle(
                        new GrantBookAccessCommand(
                            userId: $userId,
                            bookId: $bookId,
                            stripePaymentIntentId: $paymentIntentId,
                        ),
                    );
                }
            }
        }
    }

    /**
     * @throws DateMalformedStringException
     */
    private function handleSubscriptionUpdate(object $subscription): void
    {
        $userId = (int)($subscription->metadata?->user_id ?? 0);

        if (0 === $userId) {
            Log::warning('Stripe webhook: subscription missing user_id', [
                'subscription_id' => $subscription->id,
            ]);
            return;
        }

        $expiresAt = new DateTimeImmutable('@' . $subscription->current_period_end);

        $this->grantSubscription->handle(
            new GrantSubscriptionCommand(
                userId: $userId,
                stripeSubscriptionId: $subscription->id,
                expiresAt: $expiresAt,
            ),
        );
    }
}
