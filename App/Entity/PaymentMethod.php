<?php

namespace App\Entity;

use CoreDB\Kernel\Database\DataType\Checkbox;
use CoreDB\Kernel\Model;
use CoreDB\Kernel\Database\DataType\TableReference;
use CoreDB\Kernel\Database\DataType\ShortText;
use CoreDB\Kernel\Database\DataType\Date;

/**
 * Object relation with table payment_methods
 * @author murat
 */

class PaymentMethod extends Model
{
    /**
    * @var TableReference $user
    * User reference.
    */
    public TableReference $user;
    /**
    * @var ShortText $card_number
    * Card number.
    */
    public ShortText $card_number;
    /**
    * @var ShortText $card_holder
    * Card holder name.
    */
    public ShortText $card_holder;
    /**
    * @var Date $card_expire
    * Card expiry date.
    */
    public Date $card_expire;
    /**
    * @var ShortText $card_cvv
    * Card cvv.
    */
    public ShortText $card_cvv;
    /**
    * @var Checkbox $verified
    * Is verified.
    */
    public Checkbox $verified;

    /**
     * @inheritdoc
     */
    public static function getTableName(): string
    {
        return "payment_methods";
    }

    public function obfuscateCardNumber()
    {
        $cardNumberLnegth = strlen($this->card_number);
        return
        substr($this->card_number, 0, 4) . " " .
        substr($this->card_number, 4, 2) . "** " .
        "**** **" .
        substr($this->card_number, $cardNumberLnegth - 2, 2);
    }
}
