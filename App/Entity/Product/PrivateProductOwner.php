<?php

namespace App\Entity\Product;

use CoreDB\Kernel\Model;
use CoreDB\Kernel\Database\DataType\TableReference;

/**
 * Object relation with table private_product_owners
 * @author murat
 */

class PrivateProductOwner extends Model
{
    /**
    * @var TableReference $product
    *
    */
    public TableReference $product;
    /**
    * @var TableReference $owner
    * User that owns this product.
    */
    public TableReference $owner;

    /**
     * @inheritdoc
     */
    public static function getTableName(): string
    {
        return "private_product_owners";
    }
}
