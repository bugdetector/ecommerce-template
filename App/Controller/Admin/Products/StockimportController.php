<?php

namespace App\Controller\Admin\Products;

use App\Controller\Admin\ProductsController;
use App\Form\StockImportForm;
use Src\Entity\Translation;

class StockimportController extends ProductsController
{
    public function preprocessPage()
    {
        parent::preprocessPage();
        $this->setTitle(
            Translation::getTranslation("update_stock")
        );
        $this->importForm = new StockImportForm();
        $this->importForm->addClass("p-3");
        $this->importForm->processForm();
    }

    public function echoContent()
    {
        return $this->importForm;
    }
}
