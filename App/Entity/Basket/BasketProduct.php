<?php

namespace App\Entity\Basket;

use App\Entity\Analytics\ProductTracker;
use App\Entity\Product\Product;
use App\Queries\ProductFinderQuery;
use CoreDB\Kernel\Model;
use CoreDB\Kernel\Database\DataType\TableReference;
use CoreDB\Kernel\Database\DataType\FloatNumber;
use CoreDB\Kernel\Database\DataType\Integer;
use Src\Entity\Translation;
use Src\Form\Widget\FinderWidget;
use Src\Theme\View;

/**
 * Object relation with table basket_products
 * @author murat
 */

class BasketProduct extends Model
{
    /**
    * @var TableReference $basket
    * Basket reference.
    */
    public TableReference $basket;
    /**
    * @var TableReference $product
    * Reference to product.
    */
    public TableReference $product;
    /**
    * @var FloatNumber $item_vat
    * VAT price of product.
    */
    public FloatNumber $item_vat;
    /**
    * @var Integer $quantity
    * How many items ordered.
    */
    public Integer $quantity;
    /**
    * @var FloatNumber $item_per_price
    * Per price for item.
    */
    public FloatNumber $item_per_price;
    /**
    * @var FloatNumber $total_price
    * Total price.
    */
    public FloatNumber $total_price;
    /**
    * @var TableReference $variant
    * Selected product variant.
    */
    public TableReference $variant;

    /**
     * @inheritdoc
     */
    public static function getTableName(): string
    {
        return "basket_products";
    }

    public function update()
    {
        /** @var Product */
        $product = Product::get($this->product->getValue());
        if ($product) {
            /** @var Basket */
            $basket = Basket::get($this->basket);
            $this->item_per_price->setValue(
                $product->getPriceForQuantity(
                    $this->quantity->getValue(),
                    $basket->type->getValue(),
                    $basket->user->getValue()
                )
            );
            $this->item_vat->setValue(
                round(
                    $this->item_per_price->getValue() *
                    $this->quantity->getValue() *
                    $product->vat->getValue() / 100,
                    2
                )
            );
        }
        $this->total_price->setValue(
            $this->item_per_price->getValue() *
            $this->quantity->getValue()
        );
        return parent::update();
    }

    public function delete(): bool
    {
        $tracker = ProductTracker::get(["basket_product" => $this->ID->getValue()]);
        if ($tracker) {
            $tracker->delete();
        }
        return parent::delete();
    }

    public function getFormFields($name, bool $translateLabel = true): array
    {
        $formFields = parent::getFormFields($name, $translateLabel);
        if (!$this->ID->getValue()) {
            unset(
                $formFields["item_vat"],
                $formFields["item_per_price"],
                $formFields["total_price"],
                $formFields["last_updated"],
                $formFields["variant"]
            );
        } else {
            $formFields["last_updated"] = $this->last_updated->getWidget()
            ->setValue(date("d-m-Y H:i:s", strtotime($this->last_updated->getValue())))
            ->setLabel(Translation::getTranslation("last_updated"))
            ->addAttribute("disabled", "");
        }
        return $formFields;
    }

    protected function getFieldWidget(string $field_name, bool $translateLabel): ?View
    {
        switch ($field_name) {
            case "product":
                $widget = FinderWidget::create("");
                $widget->setFinderClass(ProductFinderQuery::class)
                ->setValue($this->product->getValue())
                ->setLabel(Translation::getTranslation("product"));
                return $widget;
            default:
                return parent::getFieldWidget($field_name, $translateLabel);
        }
    }

    public function getProduct()
    {
        return Product::get($this->product->getValue());
    }
}
