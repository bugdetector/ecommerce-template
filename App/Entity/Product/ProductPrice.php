<?php

namespace App\Entity\Product;

use CoreDB\Kernel\Database\DataType\EnumaratedList;
use CoreDB\Kernel\Model;
use CoreDB\Kernel\Database\DataType\TableReference;
use CoreDB\Kernel\Database\DataType\Integer;
use CoreDB\Kernel\Database\DataType\FloatNumber;
use Src\Entity\Variable;
use Src\Theme\View;

/**
 * Object relation with table product_price
 * @author murat
 */

class ProductPrice extends Model
{
    /**
    * PRICE_TYPE_DELIVERY description.
    */
    public const PRICE_TYPE_DELIVERY = "delivery";
    /**
    * PRICE_TYPE_COLLECTION description.
    */
    public const PRICE_TYPE_COLLECTION = "collection";
    /**
    * @var TableReference $product
    *
    */
    public TableReference $product;
    /**
    * @var Integer $item_count
    * Price available more than this count.
    */
    public Integer $item_count;
    /**
    * @var FloatNumber $price
    *
    */
    public FloatNumber $price;
    /**
    * @var EnumaratedList $price_type
    * Is this price defined for delivery or collection.
    */
    public EnumaratedList $price_type;

    /**
     * @inheritdoc
     */
    public static function getTableName(): string
    {
        return "product_price";
    }

    protected function getFieldWidget(string $field_name, bool $translateLabel): ?View
    {
        if ($field_name == "price_type" && !Variable::getByKey("collection_order_enabled")->value->getValue()) {
            return null;
        } else {
            return parent::getFieldWidget($field_name, $translateLabel);
        }
    }
}
