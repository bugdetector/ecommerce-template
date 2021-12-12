<?php

namespace App\Controller;

use App\Entity\Basket\Basket;
use App\Form\PaymentForm;
use App\Theme\CustomTheme;
use App\Views\BasketInfo;
use CoreDB\Kernel\Router;
use Src\Controller\AccessdeniedController;
use Src\Entity\Translation;

class PaymentController extends CustomTheme
{
    public ?Basket $basket;
    public ?PaymentForm $paymentForm;
    public BasketInfo $basketInfo;


    public function __construct(array $arguments)
    {
        parent::__construct($arguments);
        $this->basket = Basket::get([
            "ID" => intval(@$_GET["basket"]),
            "user" => \CoreDB::currentUser()->ID->getValue()
        ]);
    }

    public function checkAccess(): bool
    {
        return parent::checkAccess() &&
        $this->basket && $this->basket->is_checked_out->getValue();
    }

    public function getTemplateFile(): string
    {
        return "page-payment.twig";
    }

    public function preprocessPage()
    {
        $this->setTitle(
            Translation::getTranslation("payment")
        );
        if (
                !$this->basket ||
                $this->basket->paid_amount->getValue() == $this->basket->total->getValue() ||
                (
                    \CoreDB::currentUser()->pay_optional_at_checkout->getValue() &&
                    strtotime($this->basket->delivery_date->getValue()) < strtotime("-2 month 23:59:59")
                )
        ) {
            Router::getInstance()->route(AccessdeniedController::getUrl());
        }
        $this->paymentForm = new PaymentForm($this->basket);
        $this->paymentForm->processForm();
        $this->basketInfo = new BasketInfo($this->basket);
    }

    public function echoContent()
    {
        return $this->paymentForm;
    }
}
