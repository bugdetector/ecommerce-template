<?php

namespace App\Entity\Product;

class ProductDiscountList extends ProductList
{

    public function __construct(string $tableName = null, array $mapData = [])
    {
        parent::__construct($tableName, $mapData);
        $this->list->setValue(self::LIST_DISCOUNT);
    }

    public static function getTargetList(): string
    {
        return self::LIST_DISCOUNT;
    }

    public function getPrice(): float
    {
        return $this->promoted_price->getValue();
    }
}
