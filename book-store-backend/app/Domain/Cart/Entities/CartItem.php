<?php

declare(strict_types=1);

namespace App\Domain\Cart\Entities;

use App\Domain\Cart\Enums\CartItemTypeEnum;
use App\Domain\Cart\ValueObjects\CartItemId;
use App\Domain\Shared\ValueObjects\Money;

final readonly class CartItem
{
    public function __construct(
        public ?CartItemId      $id,
        public int              $cartId,
        public CartItemTypeEnum $type,
        public int              $referenceId,
        public string           $title,
        public Money            $price,
    ) {}
}
