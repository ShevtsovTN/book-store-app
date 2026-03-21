<?php

declare(strict_types=1);

namespace Tests\Fakes;

use App\Application\Cart\Interfaces\PaymentGatewayInterface;
use App\Domain\Shared\ValueObjects\Money;
use PHPUnit\Framework\Assert;

final class FakePaymentGateway implements PaymentGatewayInterface
{
    private ?int   $lastCartId = null;

    private ?Money $lastAmount = null;

    public function createSession(int $cartId, Money $amount, array $metadata = []): string
    {
        $this->lastCartId = $cartId;
        $this->lastAmount = $amount;

        return "https://fake-payment.test/session/{$cartId}";
    }

    public function assertSessionCreatedFor(int $cartId): void
    {
        Assert::assertSame($cartId, $this->lastCartId);
    }

    public function assertAmount(int $expectedAmount): void
    {
        Assert::assertSame($expectedAmount, $this->lastAmount?->amount);
    }
}
