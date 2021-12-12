<?php

namespace App\Entity\Analytics;

use CoreDB\Kernel\Model;
use CoreDB\Kernel\Database\DataType\TableReference;
use CoreDB\Kernel\Database\DataType\ShortText;
use CoreDB\Kernel\Database\DataType\Text;

/**
 * Object relation with table product_tracker
 * @author murat
 */

class ProductTracker extends Model
{
    /**
    * @var TableReference $basket_product
    * Product reference.
    */
    public TableReference $basket_product;
    /**
    * @var ShortText $place
    * Product list placement.
    */
    public ShortText $place;
    /**
    * @var Text $url
    * Url that user clicked.
    */
    public Text $url;

    /**
     * @inheritdoc
     */
    public static function getTableName(): string
    {
        return "product_tracker";
    }
}
