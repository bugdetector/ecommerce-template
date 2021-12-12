<?php

namespace App\Controller;

use App\Form\ProductSearchForm;
use App\Queries\ProductsQuery;
use App\Theme\CustomTheme;
use Src\Entity\Translation;

class ProductsController extends CustomTheme
{

    protected ProductSearchForm $productListSearch;

    public function checkAccess(): bool
    {
        $currentUser = \CoreDB::currentUser();
        if (!$currentUser->isLoggedIn()) {
            return true;
        } else {
            return parent::checkAccess();
        }
    }

    public function preprocessPage()
    {
        $this->setTitle(Translation::getTranslation("products"));
        $this->productListSearch = ProductSearchForm::createByObject(
            ProductsQuery::getInstance()
        );
        $this->productListSearch->addClass("p-3");
    }

    public function echoContent()
    {
        return $this->productListSearch;
    }
}
