<?php

namespace App\Controller\Admin\Products;

use App\Controller\Admin\ProductsController;
use App\Entity\Product\Enquirement;
use Src\Entity\Translation;
use Src\Form\SearchForm;

class EnquirementController extends ProductsController
{
    public $enquirementSearch;

    public function preprocessPage()
    {
        $this->setTitle(Translation::getTranslation("enquiry"));
        $enquirement = new Enquirement();
        $this->enquirementSearch = SearchForm::createByObject($enquirement);
        $this->enquirementSearch->addClass("p-3");
        $this->actions = $enquirement->actions();
    }

    public function echoContent()
    {
        return $this->enquirementSearch;
    }
}
