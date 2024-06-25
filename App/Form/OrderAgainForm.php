<?php

namespace App\Form;

use App\Controller\AjaxController;
use App\Entity\Basket\Basket;
use Src\Entity\Translation;
use Src\Form\Form;
use Src\Views\Link;
use Src\Views\TextElement;

class OrderAgainForm extends Form
{
    public function __construct(Basket $basket = null)
    {
        parent::__construct();
        if ($basket) {
            $this->addField(
                Link::create(
                    AjaxController::getUrl() . "orderAgain?basket-id={$basket->ID}",
                    TextElement::create(
                        Translation::getTranslation("order_again") . " <i class='fa fa-redo'></i>"
                    )->setIsRaw(true)
                )->addClass("btn btn-outline-primary")
            );
        }
    }

    public function getFormId(): string
    {
        return "order-again-form";
    }

    public function validate(): bool
    {
        return true;
    }

    public function submit()
    {
    }
}
