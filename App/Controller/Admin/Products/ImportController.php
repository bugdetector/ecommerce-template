<?php

namespace App\Controller\Admin\Products;

use App\Controller\Admin\ProductsController;
use App\Form\ProductsImportForm;
use Src\Entity\Translation;

class ImportController extends ProductsController
{
    public ProductsImportForm $importForm;

    public function preprocessPage()
    {
        parent::preprocessPage();
        $this->setTitle(
            Translation::getTranslation("import")
        );
        $this->importForm = new ProductsImportForm();
        $this->importForm->addClass("p-3");
        $this->importForm->processForm();
    }

    public function echoContent()
    {
        return $this->importForm;
    }
}
