<?php

namespace App\Views;

use App\Entity\Basket\BasketProduct;
use App\Entity\Product\Product;
use App\Entity\Product\VariationOption;
use Src\Theme\View;

class BasketProductCard extends View
{
    public BasketProduct $basketProduct;
    public ?Product $product;
    public bool $controlEnabled;
    public $variantName;

    public function __construct(BasketProduct $basketProduct, bool $controlEnabled = true)
    {
        $this->basketProduct = $basketProduct;
        $this->controlEnabled = $controlEnabled;
        $this->product = Product::get($basketProduct->product);
        $this->addClass("card shadow my-3 basket-product-card basket-item");
        $controller = \CoreDB::controller();
        $controller->addJsFiles("ecommerce_theme/src/components/basket-product-card/basket-product-card.js");
        $controller->addCssFiles("ecommerce_theme/src/components/basket-product-card/basket-product-card.css");
        $variantId = $this->basketProduct->variant->getValue();
        $this->variantName = $variantId ? VariationOption::get($variantId)->title->getValue() : "";
    }

    public static function create(BasketProduct $basketProduct, bool $controlEnabled = true)
    {
        return new BasketProductCard($basketProduct, $controlEnabled);
    }

    public function getTemplateFile(): string
    {
        return "basket-product-card.twig";
    }
}
