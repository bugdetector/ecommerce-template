<?php

namespace App\Entity\Log;

use CoreDB\Kernel\Database\DataType\Checkbox;
use CoreDB\Kernel\Database\DataType\FloatNumber;
use CoreDB\Kernel\Model;
use CoreDB\Kernel\Database\DataType\TableReference;
use CoreDB\Kernel\Database\DataType\LongText;
use CoreDB\Kernel\Database\DataType\ShortText;

/**
 * Object relation with table payment_log
 * @author murat
 */

class PaymentLog extends Model
{
    /**
    * @var TableReference $order
    * Order reference.
    */
    public TableReference $order;
    /**
    * @var FloatNumber $amount
    * Paid amount
    */
    public FloatNumber $amount;
    /**
    * @var ShortText $transaction_ref
    * Transaction reference.
    */
    public ShortText $transaction_ref;
    /**
    * @var Checkbox $is_success
    * Payment is successful.
    */
    public Checkbox $is_success;
    /**
    * @var LongText $response
    * Response in JSON format.
    */
    public LongText $response;

    /**
     * @inheritdoc
     */
    public static function getTableName(): string
    {
        return "payment_log";
    }
}
