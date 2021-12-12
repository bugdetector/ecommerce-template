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
        $controller->addJsFiles("dist/product-teaser/product-teaser.js");
        $controller->addCssFiles("dist/product-teaser/product-teaser.css");
    }

    public function getTemplateFile(): string
    {
        return "special-product-teaser-card.twig";
    }
}
