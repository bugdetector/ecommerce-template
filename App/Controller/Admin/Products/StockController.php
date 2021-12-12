<?php

namespace App\Controller\Admin\Products;

use App\Controller\Admin\ProductsController;
use App\Queries\StockQuery;
use Src\Entity\Translation;
use Src\Form\SearchForm;

class StockController extends ProductsController
{

    public SearchForm $stockSearch;
    public function preprocessPage()
    {
        $stockQuery = StockQuery::getInstance();
        $this->stockSearch = SearchForm::createByObject($stockQuery);
        $this->setTitle(
            Translation::getTranslation("show_stock")
        );
    }

    public function echoContent()
    {
        return $this->stockSearch;
    }
}
