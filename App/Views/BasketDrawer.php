<?php

namespace App\Views;

use Src\Theme\View;

class BasketDrawer extends View
{
    public function getTemplateFile(): string
    {
        return "basket-drawer.twig";
    }
}
