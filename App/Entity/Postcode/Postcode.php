<?php

namespace App\Entity\Postcode;

use CoreDB\Kernel\Model;
use CoreDB\Kernel\Database\DataType\ShortText;
use CoreDB\Kernel\Database\DataType\FloatNumber;
use CoreDB\Kernel\EntityReference;

/**
 * Object relation with table postcode
 * @author murat
 */

class Postcode extends Model
{
    /**
    * @var ShortText $postcode
    * Potstcode value.
    */
    public ShortText $postcode;
    /**
    * @var FloatNumber $minimum_order_price
    * If basket total is under minimum order price, delivery charge will applied.
    */
    public FloatNumber $minimum_order_price;
    /**
    * @var FloatNumber $delivery
    * Delivery charge.
    */
    public FloatNumber $delivery;

    public EntityReference $day;

    /**
     * @inheritdoc
     */
    public static function getTableName(): string
    {
        return "postcode";
    }
}
