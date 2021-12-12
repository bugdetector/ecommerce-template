<?php

namespace App\Entity;

use App\Controller\Admin\AjaxController;
use App\Controller\Admin\Branch\InsertController;
use CoreDB\Kernel\Database\DataType\Integer;
use CoreDB\Kernel\Database\DataType\LongText;
use CoreDB\Kernel\Model;
use CoreDB\Kernel\Database\DataType\ShortText;
use CoreDB\Kernel\Database\DataType\Text;
use CoreDB\Kernel\Database\DataType\TableReference;
use Src\Entity\DynamicModel;
use Src\Entity\TreeEntityAbstract;
use Src\Entity\Variable;
use Src\Theme\View;

/**
 * Object relation with table branches
 * @author murat
 */

class Branch extends TreeEntityAbstract
{
    /**
    * @var ShortText $name
    * Branch name.
    */
    public ShortText $name;
    /**
    * @var Text $address
    * Branch's address.
    */
    public Text $address;
    /**
    * @var ShortText $town
    * Branch's town.
    */
    public ShortText $town;
    /**
    * @var ShortText $county
    * Branch's county.
    */
    public ShortText $county;
    /**
    * @var ShortText $postalcode
    * Branch's postalcode.
    */
    public ShortText $postalcode;
    /**
    * @var TableReference $country
    * Branch's Country
    */
    public TableReference $country;
    /**
    * @var LongText $opening_hours
    * Opening hours of branch.
    */
    public LongText $opening_hours;
    /**
    * @var ShortText $email
    * Branch's mail.
    */
    public ShortText $email;
    /**
    * @var ShortText $phone
    * Branch's phone.
    */
    public ShortText $phone;
    /**
    * @var Integer $weight
    * Order weight.
    */
    public Integer $weight;


    public static function getTreeFieldName(): string
    {
        return "name";
    }

    public function getRemoveServiceUrl(): string
    {
        return AjaxController::getUrl() . "removeBranch";
    }

    public static function hasSubItems()
    {
        return false;
    }

    /**
     * @inheritdoc
     */
    public static function getTableName(): string
    {
        return "branches";
    }

    protected function getFieldWidget(string $field_name, bool $translateLabel): ?View
    {
        if ($field_name == "weight") {
            return null;
        } else {
            return parent::getFieldWidget($field_name, $translateLabel);
        }
    }

    public function editUrl($value = null)
    {
        return InsertController::getUrl() . ($value ?: $this->ID);
    }

    public function __toString()
    {
        $data = $this->toArray();
        unset($data["opening_hours"], $data["email"], $data["phone"], $data["weight"]);
        $data["country"] = DynamicModel::get($this->country->getValue() ?: [
            "code" =>  Variable::getByKey("default_country_code")->value->getValue()
        ], "countries")->name;
        return implode(", ", $data);
    }
}
