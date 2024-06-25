<?php

namespace App\Exception;

use App\Entity\Product\Product;
use Exception;
use Src\Entity\Translation;

class BasketException extends Exception
{
    public const TYPE_MAXIMUM_ORDER = 1;
    public const TYPE_MINIMUM_ORDER = 2;
    public const TYPE_STOCK_EXCEED = 3;
    public const INSUFFICENT_QUANTITY = 4;

    public int $type;
    private Product $product;

    public function __construct(int $type, Product $product)
    {
        $this->type = $type;
        $this->product = $product;
        switch ($this->type) {
            case self::TYPE_MAXIMUM_ORDER:
                parent::__construct(
                    Translation::getTranslation(
                        "maximum_order_warning",
                        [
                            $product->maximum_order_count->getValue(),
                            $product->title->getValue()
                        ]
                    )
                );
                break;
            case self::TYPE_STOCK_EXCEED:
                $message = $product->getQuantityInStock() ?
                Translation::getTranslation(
                    "stock_available_warning",
                    [
                        $product->getQuantityInStock(),
                        $product->title->getValue()
                    ]
                ) : Translation::getTranslation("no_stock", [
                    $product->title->getValue()
                ]);
                parent::__construct($message);
                break;
            case self::TYPE_MINIMUM_ORDER:
                parent::__construct(
                    Translation::getTranslation(
                        "minimum_order_warning",
                        [
                            $product->minimum_order_count->getValue(),
                            $product->title->getValue()
                        ]
                    )
                );
                break;
            case self::INSUFFICENT_QUANTITY:
                parent::__construct(
                    Translation::getTranslation(
                        "%d %s insufficent_quantity",
                        [
                            $product->getQuantityInStock(),
                            $product->title->getValue()
                        ]
                    )
                );
                break;
        }
    }

    public function getQuantity()
    {
        switch ($this->type) {
            case self::TYPE_STOCK_EXCEED:
                return $this->product->getQuantityInStock();
            case self::TYPE_MAXIMUM_ORDER:
                return $this->product->maximum_order_count->getValue();
            case self::TYPE_MINIMUM_ORDER:
                return $this->product->minimum_order_count->getValue();
        }
    }
}
