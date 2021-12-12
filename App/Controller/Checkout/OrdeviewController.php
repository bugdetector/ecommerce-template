<?php

namespace App\Controller\Checkout;

use App\Controller\CheckoutController;
use App\Entity\Basket\Basket;
use App\Form\OrderAgainForm;
use Src\Entity\Translation;
use Src\Form\Form;

class OrdeviewController extends CheckoutController
{
    public ?Form $form;
    protected bool $cardsEditable = false;

    public function preprocessPage()
    {
        parent::preprocessPage();
        $this->setTitle(
            Translation::getTranslation("view") . " | " .
            Translation::getTranslation("order_id")
            . ": #" . $this->basket->order_id .
            " - " . Translation::getTranslation("Cart Id") .
            ": #" . $this->basket->ID
        );
        $this->addJsCode('$(document).ready(function(){
            $("#checkout-page [data-item]").attr("data-item", "");
            $("#checkout-page .basket-subtotal").removeClass("basket-subtotal");
            $("#checkout-page .online-discount-value").removeClass("online-discount-value");
            $("#checkout-page .online-payment-discount").removeClass("online-payment-discount");
            $("#checkout-page .delivery-value").removeClass("delivery-value");
            $("#checkout-page .vat-value").removeClass("vat-value");
            $("#checkout-page .basket-total-value").removeClass("basket-total-value");
        })');
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
        return new OrderAgainForm($this->basket);
    }

    public function getTemplateFile(): string
    {
        return "page-checkout.twig";
    }
}
