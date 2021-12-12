<?php

namespace App\Controller\Checkout;

use App\Theme\CustomTheme;
use Src\Entity\Translation;

class EmptybasketController extends CustomTheme
{
    public function preprocessPage()
    {
        $this->setTitle(Translation::getTranslation("basket"));
    }

    public function getTemplateFile(): string
    {
        return "page-empty-basket.twig";
    }
}
