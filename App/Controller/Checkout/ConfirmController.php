<?php

namespace App\Controller\Checkout;

use App\Controller\CheckoutController;
use App\Entity\Basket\Basket;
use App\Form\SendOrderForm;
use CoreDB;
use Src\Entity\Translation;
use Src\Entity\Variable;
use Src\Form\Form;
use Src\Views\CollapsableCard;
use Src\Views\ViewGroup;

class ConfirmController extends CheckoutController
{
    public ?Form $form;
    protected bool $cardsEditable = false;

    public function checkAccess(): bool
    {
        return parent::checkAccess() && (
            $this->basket ? $this->basket->is_checked_out->getValue() : true
        );
    }
    public function preprocessPage()
    {
        parent::preprocessPage();
        $this->setTitle(Translation::getTranslation("confirm_order"));
    }

    protected function getBasket(): ?Basket
    {
        if (
            !CoreDB::currentUser()->isLoggedIn() &&
            Variable::getByKey("non_login_order")->value->getValue() == 1 &&
            @$_COOKIE["basket"]
        ) {
            return Basket::get([
                "ID" => intval(@$_GET["basket"]),
                "is_ordered" => 0,
                "basket_cookie" => @$_COOKIE["basket"]
            ]);
        } else {
            return Basket::get([
                "ID" => intval(@$_GET["basket"]),
                "is_ordered" => 0,
                "user" => \CoreDB::currentUser()->ID->getValue()
            ]);
        }
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
        if ($this->privateProducts) {
            return ViewGroup::create("div", "")
            ->addField($this->privateProducts)
            ->addField(CollapsableCard::create(
                Translation::getTranslation("sundries")
            )->setContent(
                $this->basketProductCards
            )->setId("sundries")
            ->setOpened(true));
            ;
        } else {
            return $this->basketProductCards;
        }
    }
}
