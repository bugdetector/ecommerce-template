<?php

namespace App\Entity;

use CoreDB\Kernel\Model;
use CoreDB\Kernel\Database\DataType\TableReference;

/**
 * Object relation with table banner_products
 * @author murat
 */

class BannerProduct extends Model
{
    /**
    * @var TableReference $banner
    * Banner reference.
    */
    public TableReference $banner;
    /**
    * @var TableReference $product
    *
    */
    public TableReference $product;

    /**
     * @inheritdoc
     */
    public static function getTableName(): string
    {
        return "banner_products";
    }
}
