<?php

namespace App\Entity;

use CoreDB\Kernel\Model;
use CoreDB\Kernel\Database\DataType\TableReference;
use CoreDB\Kernel\Database\DataType\Text;
use CoreDB\Kernel\Database\DataType\ShortText;
use CoreDB\Kernel\Database\DataType\Checkbox;
use Src\Entity\DynamicModel;
use Src\Entity\Translation;
use Src\Entity\Variable;
use Src\Form\Widget\SelectWidget;
use Src\Theme\View;

/**
 * Object relation with table custom_user_address
 * @author murat
 */

class UserAddress extends Model
{
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
    * @var TableReference $user
    *
    */
    public TableReference $user;
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
    * @var Checkbox $default
    *
    */
    public Checkbox $default;

    /**
     * @inheritdoc
     */
    public static function getTableName(): string
    {
        return "user_address";
    }

    public static function get($filter, $isDefault = true)
    {
        if ($isDefault && static::class == UserAddress::class) {
            @$filter["default"] = 1;
        }
        return parent::get($filter);
    }

    public static function getAll(array $filter, $isDefault = true): array
    {
        if ($isDefault && static::class == UserAddress::class) {
            @$filter["default"] = 1;
        }
        return parent::getAll($filter);
    }

    public function save()
    {
        if (get_class($this) == UserAddress::class) {
            $this->default->setValue(1);
        }
        if (!IS_CLI && !$this->account_number->getValue()) {
            $this->account_number->setValue(
                $this->getNewAccountNumber()
            );
        }
        $result = parent::save();
        return $result;
    }

    private function getNewAccountNumber()
    {
        $maxAccountNo = \CoreDB::database()->select(UserAddress::getTableName())
        ->selectWithFunction(["MAX(account_number)"])
        ->condition("account_number", "W0%", "LIKE")
        ->execute()->fetchColumn();
        $maxNumber = filter_var($maxAccountNo, FILTER_SANITIZE_NUMBER_INT);
        return "W" .  ($maxNumber ? str_pad($maxNumber + 1, 5, '0', STR_PAD_LEFT) : 1);
    }

    protected function getFieldWidget(string $field_name, bool $translateLabel): ?View
    {
        if ($field_name == "country") {
            $widget = new SelectWidget("");
            $widget->setOptions(
                \CoreDB::database()->select("countries", "c")
                ->select("c", ["ID", "name"])
                ->execute()->fetchAll(\PDO::FETCH_KEY_PAIR)
            )->setValue(
                $this->country->getValue() ?:
                DynamicModel::get([
                    "code" => Variable::getByKey("default_country_code")->value->getValue()
                ], "countries")->ID->getValue()
            )
            ->addAttribute("data-live-search", "true")
            ->setLabel(
                Translation::getTranslation($field_name)
            );
            return $widget;
        } elseif ($field_name == "account_number") {
            return parent::getFieldWidget($field_name, $translateLabel)
            ->addAttribute("disabled", "true");
        } elseif ($field_name == "default") {
            return null;
        } else {
            return parent::getFieldWidget($field_name, $translateLabel);
        }
    }

    public function __toString()
    {
        $data = $this->toArray();
        unset(
            $data["default"],
            $data["user"],
            $data["phone"],
            $data["mobile"]
        );
        $data = array_filter($data);
        $data["country"] = DynamicModel::get($this->country->getValue() ?: ["code" => "GB"], "countries")->name;
        return implode(", ", $data);
    }
}
