<?php

namespace App\Entity\Product;

use CoreDB\Kernel\Database\DataType\ShortText;
use CoreDB\Kernel\Model;
use CoreDB\Kernel\Database\DataType\TableReference;

/**
 * Object relation with table product_variants
 * @author murat
 */

class ProductVariant extends Model
{
    /**
    * @var TableReference $product
    * Base product.
    */
    public TableReference $product;
    /**
    * @var TableReference $variant
    * Product that is variant.
    */
    public TableReference $variant;

    /**
     * @inheritdoc
     */
    public static function getTableName(): string
    {
        return "product_variants";
    }
}
