<?php

namespace App\Views\ProductList;

use App\Controller\Products\ListController;
use App\Entity\Product\Product;
use App\Entity\Product\ProductPrice;
use CoreDB\Kernel\Database\QueryCondition;
use CoreDB\Kernel\Database\SelectQueryPreparerAbstract;
use Src\Entity\Translation;

class LatestOffers extends SwiperProductList
{
    public function getListId(): string
    {
        return "latest_offers";
    }

    public function getTitle()
    {
        return Translation::getTranslation("latest_offers");
    }

    public function getClickUrl()
    {
        return ListController::getUrl() . "latest_offers";
    }

    public function getQuery(): SelectQueryPreparerAbstract
    {
        $query = \CoreDB::database()->select(Product::getTableName(), "p")
        ->join(ProductPrice::getTableName(), "pp", "pp.product = p.ID")
        ->condition("p.sprice_valid_to", date("Y-m-d"), ">=")
        ->orderBy("p.weight DESC, p.stockcode ASC")
        ->select("p", ["ID"])
        ->selectWithFunction(["COUNT(*) as price_count"])
        ->groupBy("p.ID")
        ->condition("p.published", 1)
        ->having("price_count > 2")
        ->limit(9);
        $validFromCondition = new QueryCondition($query);
        $validFromCondition->condition("p.sprice_valid_from", date("Y-m-d"), "<=")
        ->condition("p.sprice_valid_from", null, "IS", "OR");
        $query->condition($validFromCondition);
        return $query;
        ;
    }
}
