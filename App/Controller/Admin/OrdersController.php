<?php

namespace App\Controller\Admin;

use App\AdminTheme\EcommerceAdminTheme;
use App\Entity\Basket\Basket;
use App\Queries\OrdersQuery;
use CoreDB;
use Src\Entity\Translation;
use Src\Form\SearchForm;

class OrdersController extends EcommerceAdminTheme
{

    public $searchForm;
    
    public function checkAccess(): bool
    {
        return parent::checkAccess() ||
        CoreDB::currentUser()->isUserInRole('Order Manager');
    }
    public function preprocessPage()
    {
        $this->setTitle(Translation::getTranslation("orders"));
        $this->searchForm = SearchForm::createByObject(OrdersQuery::getInstance());
        $this->searchForm->addClass("p-3");

        $this->actions = (new Basket())->actions();
    }

    public function getTemplateFile(): string
    {
        return "page-admin-products.twig";
    }

    public function echoContent()
    {
        return $this->searchForm;
    }
}
