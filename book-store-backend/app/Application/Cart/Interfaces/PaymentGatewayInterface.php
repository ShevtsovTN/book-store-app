<?php

declare(strict_types=1);

namespace App\Application\Cart\Interfaces;

use App\Domain\Shared\ValueObjects\Money;

interface PaymentGatewayInterface
{
    public function createSession(int $cartId, Money $amount, array $metadata = []): string;
}
