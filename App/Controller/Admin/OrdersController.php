<?php

namespace App\Controller\Admin;

use App\AdminTheme\EcommerceAdminController;
use App\Entity\Basket\Basket;
use App\Queries\OrdersQuery;
use CoreDB;
use Src\Entity\Translation;
use Src\Form\SearchForm;
use Src\Views\BasicCard;
use Src\Views\ViewGroup;

class OrdersController extends EcommerceAdminController
{

    public $searchForm;
    
    public function checkAccess(): bool
    {
        return parent::checkAccess() ||
        CoreDB::currentUser()->isUserInRole('Order Manager');
    }
    public function preprocessPage()
    {
        $waitingApproval = \CoreDB::database()->select(Basket::getTableName(), 'b')
        ->condition('b.status', Basket::STATUS_WAITING_APPROVAL)
        ->selectWithFunction(['COUNT(*) AS COUNT']);
        $approved = \CoreDB::database()->select(Basket::getTableName(), 'b')
        ->condition('b.status', Basket::STATUS_APPROVED)
        ->selectWithFunction(['COUNT(*) AS COUNT']);
        $onDelivery = \CoreDB::database()->select(Basket::getTableName(), 'b')
        ->condition('b.status', Basket::STATUS_ON_DELIVERY)
        ->selectWithFunction(['COUNT(*) AS COUNT']);
        $delivered = \CoreDB::database()->select(Basket::getTableName(), 'b')
        ->condition('b.status', Basket::STATUS_DELIVERED)
        ->selectWithFunction(['COUNT(*) AS COUNT']);
        $this->setTitle(Translation::getTranslation("orders"));
        $this->searchForm = SearchForm::createByObject(OrdersQuery::getInstance());
        $this->searchForm->addClass("p-3");

        $this->actions = (new Basket())->actions();
        $this->waitingApproved = $waitingApproval->execute()->fetchColumn();
        $this->approved = $approved->execute()->fetchColumn();
        $this->onDelivery = $onDelivery->execute()->fetchColumn();
        $this->delivered = $delivered->execute()->fetchColumn();
    }

    public function echoContent()
    {
        return ViewGroup::create("div", "")
        ->addField(
            ViewGroup::create("div", "row p-3")
            ->addField(
                BasicCard::create()
                ->setBackgroundClass("bg-danger")
                ->setHref(
                    self::getUrl() . "?status=" . Basket::STATUS_WAITING_APPROVAL
                )
                ->setTitle(Translation::getTranslation("waiting_approval"))
                ->setDescription($this->waitingApproved)
                ->setIconClass("fa fa-shopping-basket")
                ->addClass("col-lg-3 col-md-6 mb-4")
            )
            ->addField(
                BasicCard::create()
                ->setBackgroundClass("bg-info")
                ->setHref(
                    self::getUrl() . "?status=" . Basket::STATUS_APPROVED
                )
                ->setTitle(Translation::getTranslation("approved"))
                ->setDescription($this->approved)
                ->setIconClass("fa fa-shopping-basket")
                ->addClass("col-lg-3 col-md-6 mb-4")
            )
            ->addField(
                BasicCard::create()
                ->setBackgroundClass("bg-warning")
                ->setHref(
                    self::getUrl() . "?status=" . Basket::STATUS_ON_DELIVERY
                )
                ->setTitle(Translation::getTranslation("on_delivery"))
                ->setDescription($this->onDelivery)
                ->setIconClass("fa fa-shopping-basket")
                ->addClass("col-lg-3 col-md-6 mb-4")
            )
            ->addField(
                BasicCard::create()
                ->setBackgroundClass("bg-primary")
                ->setHref(
                    self::getUrl() . "?status=" . Basket::STATUS_DELIVERED
                )
                ->setTitle(Translation::getTranslation("delivered"))
                ->setDescription($this->delivered)
                ->setIconClass("fa fa-shopping-basket")
                ->addClass("col-lg-3 col-md-6 mb-4")
            )
        )->addField(
            $this->searchForm
        );
    }
}
