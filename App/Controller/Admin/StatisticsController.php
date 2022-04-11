<?php

namespace App\Controller\Admin;

use App\AdminTheme\EcommerceAdminController;
use App\Controller\Admin\Statistics\ProducttrackerController;
use Src\Entity\Translation;
use Src\Views\BasicCard;

class StatisticsController extends EcommerceAdminController
{
    public function preprocessPage()
    {
        $this->setTitle(Translation::getTranslation("statistics"));
        $this->cards[] = BasicCard::create()
        ->setBackgroundClass("bg-primary")
        ->setHref(ProducttrackerController::getUrl())
        ->setTitle(Translation::getTranslation("product_tracker"))
        ->setIconClass("fa-chart-area")
        ->addClass("col-lg-3 col-md-6 mb-4");
    }
}
