<?php

namespace App\Controller\Checkout;

use App\Controller\CheckoutController;
use App\Controller\PaymentController;
use App\Controller\ProductsController;
use App\Entity\Basket\Basket;
use App\Form\SendOrderForm;
use CoreDB\Kernel\Router;
use Src\Controller\AccessdeniedController;
use Src\Entity\Translation;
use Src\Entity\Variable;
use Src\Form\Form;
use Src\Views\Link;
use Src\Views\TextElement;
use Src\Views\ViewGroup;

class SentController extends CheckoutController
{
    public ?Form $form;
    protected bool $cardsEditable = false;

    public function preprocessPage()
    {
        parent::preprocessPage();
        if (
            $this->basket->paid_amount->getValue() == $this->basket->total->getValue() ||
            strtotime($this->basket->delivery_date->getValue()) < strtotime("-2 month 23:59:59")
        ) {
            Router::getInstance()->route(AccessdeniedController::getUrl());
        }
        $this->setTitle(
            Translation::getTranslation("order_sent") . " : #" . $this->basket->order_id->getValue()
        );
    }

    protected function getBasket(): ?Basket
    {
        return Basket::get([
            "ID" => intval(@$_GET["basket"]),
            "is_ordered" => 1,
            "user" => \CoreDB::currentUser()->ID->getValue()
        ]);
    }

    protected function getForm(): ?Form
    {
        if (!$this->basket->is_ordered->getValue()) {
            return new SendOrderForm($this->basket);
        } else {
            return null;
        }
    }

    public function echoContent()
    {
        $parentContent = parent::echoContent();
        $buttonGroup = ViewGroup::create("div", "row p-3")
        ->addField(
            ViewGroup::create("div", "col-6")
            ->addField(
                Link::create(
                    ProductsController::getUrl(),
                    TextElement::create(
                        "<i class='fa fa-arrow-left'></i> " .
                        Translation::getTranslation("pay_at", [
                            Translation::getTranslation($this->basket->type->getValue())
                        ])
                    )->setIsRaw(true)
                )->addClass("btn btn-outline-info form-control h-100")
            )
        );
        if (
            $this->basket->type->getValue() != Basket::TYPE_COLLECTION ||
            $this->basket->total->getValue() <= Variable::getByKey("collection_max_payment")->value->getValue()
        ) {
            $currencyIcon = Variable::getByKey("currency_icon")->value->getValue();
            $buttonGroup->addField(
                ViewGroup::create("div", "col-6")
                ->addField(
                    Link::create(
                        PaymentController::getUrl() . "?basket={$this->basket->ID}",
                        TextElement::create(
                            Translation::getTranslation("pay_now") . " ({$currencyIcon}" .
                            (
                                $this->basket->total->getValue() -
                                $this->basket->paid_amount->getValue()
                            )
                            . ") <i class='fa fa-check'></i>"
                        )->setIsRaw(true)
                    )->addClass("btn btn-primary form-control h-100")
                )
            );
        }
        return ViewGroup::create("div", "")
        ->addField($buttonGroup)
        ->addField($parentContent);
    }
}
