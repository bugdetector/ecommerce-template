<?php

namespace App\Entity\Basket;

use CoreDB\Kernel\Model;
use CoreDB\Kernel\Database\DataType\Text;
use CoreDB\Kernel\Database\DataType\ShortText;
use CoreDB\Kernel\Database\DataType\TableReference;
use Src\Entity\DynamicModel;
use Src\Entity\Variable;

/**
 * Object relation with table order_address
 * @author murat
 */

class OrderAddress extends Model
{
    /**
    * @var TableReference $order
    *
    */
    public TableReference $order;
    /**
    * @var ShortText $account_number
    * Unique account number
    */
    public ShortText $account_number;
    /**
    * @var ShortText $company_name
    *
    */
    public ShortText $company_name;
    /**
    * @var Text $address
    *
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
    * @var TableReference $country
    *
    */
    public TableReference $country;
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
        return "order_address";
    }

    public function __toString()
    {
        $data = $this->toArray();
        unset($data["order"], $data["account_number"], $data["phone"], $data["mobile"]);
        $data = array_filter($data, function ($el) {
            return $el && trim($el) ? true : false;
        });
        $data["country"] = DynamicModel::get($this->country->getValue() ?: [
            "code" => Variable::getByKey("default_country_code")->value->getValue()
        ], "countries")->name;
        return implode(", ", $data);
    }
}
