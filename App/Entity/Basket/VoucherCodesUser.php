<?php

namespace App\Entity\Basket;

use CoreDB\Kernel\Model;
use CoreDB\Kernel\Database\DataType\TableReference;

/**
 * Object relation with table voucher_codes_user
 * @author robokopiye
 */

class VoucherCodesUser extends Model
{
    /**
    * @var TableReference $user
    * User reference.
    */
    public TableReference $user;
    /**
    * @var TableReference $voucher_code
    * Voucher code reference.
    */
    public TableReference $voucher_code;

    /**
     * @inheritdoc
     */
    public static function getTableName(): string
    {
        return "voucher_codes_user";
    }
}
