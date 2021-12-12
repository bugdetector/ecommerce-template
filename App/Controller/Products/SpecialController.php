<?php

namespace App\Controller\Products;

use App\Controller\ProductsController;
use App\Form\ProductSearchForm;
use App\Queries\SpecialProductsQuery;
use Src\Entity\Translation;

class SpecialController extends ProductsController
{
    public function preprocessPage()
    {
        $this->setTitle(Translation::getTranslation("special_products"));
        $this->productListSearch = ProductSearchForm::createByObject(
            SpecialProductsQuery::getInstance()
        );
        $this->productListSearch->addClass("p-3");
    }
}
