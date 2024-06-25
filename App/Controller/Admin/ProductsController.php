<?php

namespace App\Controller\Admin;

use App\AdminTheme\EcommerceAdminController;
use App\Controller\Admin\Products\MissingpicturesController;
use App\Entity\Product\Product;
use App\Entity\Product\ProductPicture;
use App\Queries\AdminProductsQuery;
use Src\Entity\Translation;
use Src\Form\SearchForm;
use Src\Views\BasicCard;
use Src\Views\ViewGroup;

class ProductsController extends EcommerceAdminController
{
    protected SearchForm $productListSearch;
    public $productCount;
    public $missingPictureCount;
    public array $actions;

    public function preprocessPage()
    {
        $this->setTitle(Translation::getTranslation("products"));
        $this->productListSearch = SearchForm::createByObject(AdminProductsQuery::getInstance());
        $this->productListSearch->addClass("p-3");
        $product = new Product();
        $this->actions = $product->actions();
    }

    public function echoContent()
    {
        $this->productCount = \CoreDB::database()->select(Product::getTableName())
        ->selectWithFunction(["COUNT(*) as count"])
        ->execute()->fetchObject()->count;

        $this->missingPictureCount = \CoreDB::database()
        ->select(Product::getTableName(), 'p')
        ->leftjoin(ProductPicture::getTableName(), 'pp', 'p.ID = pp.product')
        ->condition("pp.ID", null, "IS")
        ->condition("pp.image", null, "IS", "OR")
        ->selectWithFunction(["COUNT(*) as count"])
        ->execute()->fetchColumn();
        return ViewGroup::create("div", "")
        ->addField(
            ViewGroup::create("div", "row p-3")
            ->addField(
                BasicCard::create()
                ->setBackgroundClass("bg-info")
                ->setHref(ProductsController::getUrl())
                ->setTitle(Translation::getTranslation("product_count"))
                ->setDescription($this->productCount)
                ->setIconClass("fa-tablets")
                ->addClass("col-lg-3 col-md-6 mb-4")
            )
            ->addField(
                BasicCard::create()
                ->setBackgroundClass("bg-warning")
                ->setHref(MissingpicturesController::getUrl())
                ->setTitle("MISSING PRODUCT PICTURES")
                ->setDescription($this->missingPictureCount)
                ->setIconClass("fa-exclamation")
                ->addClass("col-lg-3 col-md-6 mb-4")
            )
        )->addField(
            $this->productListSearch
        );
    }
}
