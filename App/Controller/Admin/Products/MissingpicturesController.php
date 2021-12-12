<?php

namespace App\Controller\Admin\Products;

use App\Controller\Admin\ProductsController;
use App\Entity\Product\Product;
use App\Queries\MissingPicturesQuery;
use Src\Form\SearchForm;

class MissingpicturesController extends ProductsController
{
    public function preprocessPage()
    {
        $this->setTitle("Missing Pictures");
        $this->productListSearch = SearchForm::createByObject(
            MissingPicturesQuery::getInstance()
        );
        $this->productListSearch->addClass("p-3");
        $product = new Product();
        $this->actions = $product->actions();
    }
}
