<?php

namespace App\Controller\Checkout;

use App\Theme\AppController;
use Src\Entity\Translation;

class EmptybasketController extends AppController
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
