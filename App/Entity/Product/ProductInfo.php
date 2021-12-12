<?php

namespace App\Entity\Product;

use CoreDB\Kernel\Model;
use CoreDB\Kernel\Database\DataType\TableReference;
use CoreDB\Kernel\Database\DataType\ShortText;
use CoreDB\Kernel\Database\DataType\LongText;

/**
 * Object relation with table product_info
 * @author murat
 */

class ProductInfo extends Model
{
    /**
    * @var TableReference $product
    * Product reference.
    */
    public TableReference $product;
    /**
    * @var ShortText $title
    * Info title
    */
    public ShortText $title;
    /**
    * @var LongText $description
    * Info description.
    */
    public LongText $description;

    /**
     * @inheritdoc
     */
    public static function getTableName(): string
    {
        return "product_info";
    }
}
