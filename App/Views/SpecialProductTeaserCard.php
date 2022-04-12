<?php

namespace App\Views;

use CoreDB;
use Src\Theme\ResultsViewer;

class SpecialProductTeaserCard extends ResultsViewer
{

    public function __construct()
    {
        $this->addClass("row");
        $controller = CoreDB::controller();
        $controller->addJsFiles("ecommerce_theme/src/components/product-teaser/product-teaser.js");
        $controller->addCssFiles("ecommerce_theme/src/components/product-teaser/product-teaser.js");
    }

    public function getTemplateFile(): string
    {
        return "special-product-teaser-card.twig";
    }
}
