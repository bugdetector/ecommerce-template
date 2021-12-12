<?php

namespace App\Controller\Admin\Statistics;

use App\Controller\Admin\AjaxController;
use App\Controller\Admin\StatisticsController;
use App\Entity\Analytics\ProductTracker;
use App\Entity\Basket\Basket;
use App\Entity\Basket\BasketProduct;
use App\Views\GraphView;
use Src\Entity\Translation;
use Src\Views\CollapsableCard;
use Src\Views\TextElement;

class ProducttrackerController extends StatisticsController
{
    public function preprocessPage()
    {
        $this->setTitle(Translation::getTranslation("product_tracker"));

        $productListCount = $places = \CoreDB::database()->select(ProductTracker::getTableName(), "pt")
        ->join(BasketProduct::getTableName(), "bp", "pt.basket_product = bp.ID")
        ->join(Basket::getTableName(), "b", "bp.basket = b.ID")
        ->condition("b.is_ordered", 1)
        ->condition("pt.last_updated", date("Y-m-01"), ">=")
        ->condition("pt.place", "product_list", "=")
        ->groupBy("pt.place")
        ->selectWithFunction([
            "COUNT(*) AS count"
        ])->execute()->fetchColumn();
        $this->cards[] = TextElement::create(
            Translation::getTranslation(
                "item_added_using_product_list",
                [$productListCount]
            )
        )->addClass("p-3 alert alert-info w-100");
        $this->cards[] = CollapsableCard::create(Translation::getTranslation("this_month_orders"))
        ->setContent(
            GraphView::create("div", "")
            ->setDataServiceUrl(AjaxController::getUrl() . "productTrackerGraph")
            ->addAttribute("style", "height: 80vh")
        )
        ->setOpened(true)
        ->addClass("w-100 p-3");
    }
}
