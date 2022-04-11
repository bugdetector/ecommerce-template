<?php

namespace App\Controller;

use App\Form\ProductSearchForm;
use App\Queries\FavoriteProductsQuery;
use App\Queries\PrivateProductsQuery;
use App\Theme\AppController;
use Src\Entity\Translation;

class FavoritesController extends AppController
{

    public ProductSearchForm $privateProductsList;
    private ProductSearchForm $productListSearch;

    public function getTemplateFile(): string
    {
        return "page-favourites.twig";
    }

    public function preprocessPage()
    {
        $this->setTitle(Translation::getTranslation("favorites"));
        $this->productListSearch = ProductSearchForm::createByObject(
            FavoriteProductsQuery::getInstance()
        );
        $this->productListSearch->addClass("p-3");
        unset($this->productListSearch->pagination);

        $this->privateProductsList = ProductSearchForm::createByObject(
            PrivateProductsQuery::getInstance()
        );
        unset($this->privateProductsList->pagination);
        $this->privateProductsList->addClass("p-3");
    }

    public function echoContent()
    {
        return $this->productListSearch;
    }
}
