<?php

namespace App\Views;

use Src\Theme\ResultsViewer;

class OrderCard extends ResultsViewer
{

    public function __construct()
    {
        $this->addClass("row");
        $controller = \CoreDB::controller();
        $controller->addJsFiles("dist/order-card/order-card.js");
        $controller->addFrontendTranslation("cancel_order");
        $controller->addFrontendTranslation("cancel_order_promt");
    }

    public function getTemplateFile(): string
    {
        return "order-card.twig";
    }
}
