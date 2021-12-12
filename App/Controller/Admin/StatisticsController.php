<?php

namespace App\Controller\Admin;

use App\Controller\Admin\Statistics\ProducttrackerController;
use App\Controller\AdminController;
use Src\Entity\Translation;
use Src\Views\BasicCard;

class StatisticsController extends AdminController
{
    public function preprocessPage()
    {
        $this->setTitle(Translation::getTranslation("statistics"));
        $this->cards[] = BasicCard::create()
        ->setBorderClass("border-left-primary")
        ->setHref(ProducttrackerController::getUrl())
        ->setTitle(Translation::getTranslation("product_tracker"))
        ->setIconClass("fa-chart-area")
        ->addClass("col-lg-3 col-md-6 mb-4");
    }
}
