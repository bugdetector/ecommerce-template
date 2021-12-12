<?php

namespace App\Entity\Product;

use App\Controller\Admin\AjaxController;
use App\Controller\Admin\Productlists\InsertController;
use App\Form\ProductListInsertForm;
use CoreDB\Kernel\Database\DataType\TableReference;
use CoreDB\Kernel\Database\DataType\EnumaratedList;
use CoreDB\Kernel\Database\DataType\Date;
use CoreDB\Kernel\Database\DataType\FloatNumber;
use CoreDB\Kernel\Database\DataType\Integer;
use Src\Entity\TreeEntityAbstract;
use Src\Theme\View;

/**
 * Object relation with table product_lists
 * @author murat
 */

abstract class ProductList extends TreeEntityAbstract
{
    /**
    * LIST_DISCOUNT description.
    */
    public const LIST_DISCOUNT = "discount";

    /**
    * @var TableReference $product
    *
    */
    public TableReference $product;
    /**
    * @var EnumaratedList $list
    * Available product lists.
    */
    public EnumaratedList $list;
    /**
    * @var Date $start_date
    * Start date of promotion.
    */
    public Date $start_date;
    /**
    * @var Date $end_date
    * End date of promotion.
    */
    public Date $end_date;
    /**
    * @var FloatNumber $promoted_price
    * Prometed price
    */
    public FloatNumber $promoted_price;
    /**
    * @var Integer $weight
    * Used for ordering.
    */
    public Integer $weight;

    /**
     * @inheritdoc
     */
    public static function getTableName(): string
    {
        return "product_lists";
    }

    public static function hasSubItems()
    {
        return false;
    }

    public function getForm()
    {
        return new ProductListInsertForm($this);
    }

    public function getRemoveServiceUrl(): string
    {
        return AjaxController::getUrl() . "removeProductListItem";
    }

    public static function getTreeFieldName(): string
    {
        return "product";
    }

    public static function getRootElements(): array
    {
        return static::findAll(["list" => static::getTargetList() ], static::getTableName(), "weight");
    }

    public function editUrl($value = null)
    {
        return InsertController::getUrl() . static::getTargetList() . "/" . ($value ?: $this->ID);
    }

    protected function getFieldWidget(string $field_name, bool $translateLabel): ?View
    {
        if (in_array($field_name, ["list", "weight"])) {
            return null;
        }
        return parent::getFieldWidget($field_name, $translateLabel);
    }

    abstract public static function getTargetList(): string;
    abstract public function getPrice(): float;

    public static function getListEntry($productID)
    {
        $promotion = \CoreDB::database()->select(ProductList::getTableName(), "pl")
        ->select("pl", ["ID", "list"])
        ->condition("pl.product", $productID)
        ->condition("pl.start_date", date("Y-m-d 00:00:00"), "<=")
        ->condition("pl.end_date", date("Y-m-d 23:59:59"), ">=")
        ->execute()->fetchObject();
        $listClass = $promotion ? self::getClassByListName($promotion->list) : null;
        return $listClass ? $listClass::get($promotion->ID) : null;
    }

    public static function getClassByListName($listName)
    {
        switch ($listName) {
            case ProductList::LIST_DISCOUNT:
                $listClass = ProductDiscountList::class;
                break;
            default:
                return null;
        }
        return $listClass;
    }
}
