<?php

namespace App\Entity\Product;

use CoreDB\Kernel\Model;
use CoreDB\Kernel\Database\DataType\TableReference;
use CoreDB\Kernel\Database\DataType\Integer;

/**
 * Object relation with table stock
 * @author murat
 */

class Stock extends Model
{
    /**
    * @var TableReference $branch
    * Branch reference.
    */
    public TableReference $branch;
    /**
    * @var TableReference $product
    * Product reference.
    */
    public TableReference $product;
    /**
    * @var Integer $quantity
    * Available quantity in stock.
    */
    public Integer $quantity;

    /**
     * @inheritdoc
     */
    public static function getTableName(): string
    {
        return "stock";
    }
}
