<?php

declare(strict_types=1);

namespace App\Infrastructure\Payment;

use App\Application\Cart\Interfaces\PaymentGatewayInterface;
use App\Domain\Shared\ValueObjects\Money;

final readonly class StripePaymentGateway implements PaymentGatewayInterface
{
    public function createSession(int $cartId, Money $amount, array $metadata = []): string
    {
        throw new \RuntimeException(
            'Stripe integration is not implemented yet.',
        );
    }
}
