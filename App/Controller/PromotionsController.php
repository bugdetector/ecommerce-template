<?php

namespace App\Controller;

use App\Form\ProductSearchForm;
use App\Queries\DiscountPromotionsQuery;
use App\Theme\AppController;
use CoreDB\Kernel\Messenger;
use Src\Entity\Translation;

class PromotionsController extends AppController
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
        $this->setTitle(
            Translation::getTranslation("promotions")
        );
        $this->productListSearch = ProductSearchForm::createByObject(
            DiscountPromotionsQuery::getInstance()
        );
        $this->productListSearch->addClass("p-3");
        if (!$this->productListSearch->data) {
            $this->createMessage("Currently there is no promotions available.", Messenger::INFO);
        }
    }

    public function echoContent()
    {
        return $this->productListSearch;
    }
}
