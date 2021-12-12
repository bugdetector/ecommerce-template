<?php

namespace App\Entity;

use CoreDB\Kernel\Model;
use CoreDB\Kernel\Database\DataType\ShortText;
use CoreDB\Kernel\Database\DataType\Text;

/**
 * Object relation with table customers
 * @author murat
 */

class Customer extends Model
{
    /**
    * @var ShortText $company_name
    *
    */
    public ShortText $company_name;
    /**
    * @var ShortText $name
    *
    */
    public ShortText $name;
    /**
    * @var ShortText $email
    *
    */
    public ShortText $email;
    /**
    * @var ShortText $account_number
    * Unique account number
    */
    public ShortText $account_number;
    /**
    * @var Text $address
    * Address
    */
    public Text $address;
    /**
    * @var ShortText $town
    *
    */
    public ShortText $town;
    /**
    * @var ShortText $county
    *
    */
    public ShortText $county;
    /**
    * @var ShortText $postalcode
    *
    */
    public ShortText $postalcode;
    /**
    * @var ShortText $phone
    *
    */
    public ShortText $phone;
    /**
    * @var ShortText $mobile
    *
    */
    public ShortText $mobile;

    /**
     * @inheritdoc
     */
    public static function getTableName(): string
    {
        return "customers";
    }
}
