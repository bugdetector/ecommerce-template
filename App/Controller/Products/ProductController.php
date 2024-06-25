<?php

namespace App\Controller\Products;

use App\Entity\Basket\Basket;
use App\Entity\Basket\BasketProduct;
use App\Entity\Product\Enquirement;
use App\Entity\Product\FavoriteProducts;
use App\Entity\Product\Product;
use App\Entity\Product\VariationOption;
use App\Theme\AppController;
use CoreDB\Kernel\Router;
use Src\Controller\AccessdeniedController;
use Src\Controller\NotFoundController;
use Src\Form\Widget\SelectWidget;

class ProductController extends AppController
{
    public ?Product $product = null;
    public bool $logged_in;
    public BasketProduct $basketProduct;
    public bool $isFavorite;
    public array $priceList;
    public bool $isEnquirementExist;
    public ?SelectWidget $variationSelect = null;

    public function checkAccess(): bool
    {
        $currentUser = \CoreDB::currentUser();
        if (!$currentUser->isLoggedIn()) {
            return true;
        } else {
            return parent::checkAccess();
        }
    }

    public function getTemplateFile(): string
    {
        return "page-product-detail.twig";
    }

    public function preprocessPage()
    {
        $this->logged_in = \CoreDB::currentUser()->isLoggedIn();
        if (isset($this->arguments[0]) && $this->arguments[0]) {
            $this->product = Product::getByUrlAlias($this->arguments[0]);
        }
        if (!$this->product) {
            Router::getInstance()->route(NotFoundController::getUrl());
        }
        if (
            !$this->product->published->getValue() ||
            !$this->product->isPrivateAndOwnerMatches()
        ) {
            Router::getInstance()->route(AccessdeniedController::getUrl());
        }
        $this->title = $this->product->title;
        if ($this->logged_in) {
            $userBasket = Basket::getUserBasket();
            $this->basketProduct = $userBasket->getBasketProduct($this->product);
            $this->isFavorite = FavoriteProducts::isProductInFavorite($this->product->ID->getValue());

            if ($this->product->is_variable->getValue()) {
                $this->variationSelect = VariationOption::getSelectField()
                ->addAttribute("data-item", $this->product->ID->getValue())
                ->addClass("variation_select");
            }

            if (!$this->product->is_special_product->getValue()) {
                $this->addJsFiles("ecommerce_theme/src/components/product-teaser/product-teaser.js");
                $this->addCssFiles("ecommerce_theme/src/components/product-teaser/product-teaser.css");
            } else {
                $this->isEnquirementExist = boolval(
                    Enquirement::getUserActiveEnquirement($this->product->ID->getValue())
                );
                $this->addJsFiles("dist/summernote/summernote.js");
                $this->addCssFiles("dist/summernote/summernote.css");
                $this->addJsFiles("dist/enquire/enquire.js");
                $this->addFrontendTranslation("quantity");
                $this->addFrontendTranslation("description");
                $this->addFrontendTranslation("enquiry");
                $this->addFrontendTranslation("send_request");
                $this->addFrontendTranslation("enquiry_description");
            }
        }
        $this->addJsFiles("ecommerce_theme/lib/swiper/swiper-bundle.min.js");
        $this->addCssFiles("ecommerce_theme/lib/swiper/swiper-bundle.min.css");
        $this->addJsFiles("ecommerce_theme/src/components/swiper/swiper.js");
        $this->addJsFiles("ecommerce_theme/lib/fancybox@3.5.7/jquery.fancybox.min.js");
        $this->addCssFiles("ecommerce_theme/lib/fancybox@3.5.7/jquery.fancybox.min.css");
        $this->addJsFiles("ecommerce_theme/src/components/fancybox/fancybox.js");
    }
}
