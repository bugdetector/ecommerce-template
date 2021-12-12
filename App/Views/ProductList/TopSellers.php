<?php

namespace App\Views\ProductList;

use App\Controller\Products\ListController;
use App\Entity\Basket\Basket;
use App\Entity\Basket\BasketProduct;
use App\Entity\Product\Product;
use CoreDB\Kernel\Database\SelectQueryPreparerAbstract;
use Src\Entity\Translation;

class TopSellers extends SwiperProductList
{

    public function getListId(): string
    {
        return "top_sellers";
    }

    public function getTitle()
    {
        return Translation::getTranslation("top_sellers");
    }

    public function getClickUrl()
    {
        return ListController::getUrl() . "top_sellers";
    }

    public function getQuery(): SelectQueryPreparerAbstract
    {
        return \CoreDB::database()->select(Product::getTableName(), "p")
            ->join(BasketProduct::getTableName(), "bp", "bp.product = p.ID")
            ->join(
                Basket::getTableName(),
                "b",
                "b.ID = bp.basket AND b.is_ordered = 1 AND b.order_time >= :one_month_ago"
            )->params([":one_month_ago" => date("Y-m-d H:i:s", strtotime("-1 month"))])
            ->select("p", ["ID"])
            ->condition("p.published", 1)
            ->groupBy("p.ID")
            ->orderBy("SUM(bp.quantity) DESC")
            ->limit(10);
    }
}
