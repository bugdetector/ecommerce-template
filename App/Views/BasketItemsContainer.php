<?php

namespace App\Views;

use App\Entity\Basket\Basket;
use Src\Theme\View;

class BasketItemsContainer extends View
{
    public Basket $basket;

    public function __construct()
    {
        $this->basket = Basket::getUserBasket();
    }

    public function getTemplateFile(): string
    {
        return "basket-items-container.twig";
    }
}
