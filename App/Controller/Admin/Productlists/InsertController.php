<?php

namespace App\Controller\Admin\Productlists;

use App\Controller\Admin\ProductlistsController;
use App\Entity\Product\ProductDiscountList;
use App\Entity\Product\ProductList;
use CoreDB\Kernel\Messenger;
use CoreDB\Kernel\Router;
use Src\Controller\NotFoundController;
use Src\Entity\Translation;
use Src\Form\InsertForm;

class InsertController extends ProductlistsController
{

    public ?ProductList $productlist;
    public InsertForm $productlistForm;

    public function preprocessPage()
    {
        $listClass = ProductList::getClassByListName(@$this->arguments[0]);
        if (!$listClass) {
            $this->createMessage(
                Translation::getTranslation("please_select_menu"),
                Messenger::INFO
            );
        }
        if (isset($this->arguments[1]) && $this->arguments[1]) {
            $this->productlist = $listClass::get($this->arguments[1]);
            if (!$this->productlist) {
                Router::getInstance()->route(NotFoundController::getUrl());
            }
            $title = Translation::getTranslation("edit") . " | " . $this->productlist->ID;
        } else {
            $this->productlist = new $listClass();
            $title = Translation::getTranslation("add") . " | " .
            Translation::getTranslation(
                $this->productlist->getTargetList()
            );
        }
        $this->setTitle($title);
        $this->productlistForm = $this->productlist->getForm();
        $this->productlistForm->processForm();
        $this->productlistForm->addClass("p-3");
    }

    public function echoContent()
    {
        return $this->productlistForm;
    }
}
