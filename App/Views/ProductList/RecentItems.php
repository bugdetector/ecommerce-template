<?php

namespace App\Views\ProductList;

use App\Controller\Products\ListController;
use App\Entity\Basket\Basket;
use App\Entity\Basket\BasketProduct;
use App\Entity\Product\Product;
use CoreDB\Kernel\Database\SelectQueryPreparerAbstract;
use Src\Entity\Cache;
use Src\Entity\Translation;

class RecentItems extends SwiperProductList
{
    public function __construct()
    {
        parent::__construct();
        \CoreDB::database()->delete(Cache::getTableName())
        ->condition("key", $this->getListId())->execute();
    }

    public function getListId(): string
    {
        return "recent_items_" . \CoreDB::currentUser()->ID->getValue();
    }

    public function getTitle()
    {
        return Translation::getTranslation("recent_items");
    }

    public function getClickUrl()
    {
        return ListController::getUrl() . "recent_items";
    }

    public function getQuery(): SelectQueryPreparerAbstract
    {
        return \CoreDB::database()->select(Product::getTableName(), "p")
            ->join(BasketProduct::getTableName(), "bp", "bp.product = p.ID")
            ->join(
                Basket::getTableName(),
                "b",
                "b.ID = bp.basket AND b.is_ordered = 1 AND b.order_time >= :three_months_ago " .
                "AND b.user = :user"
            )->params([
                ":three_months_ago" => date("Y-m-d H:i:s", strtotime("-3 month")),
                ":user" => \CoreDB::currentUser()->ID->getValue()
                ])
            ->select("p", ["ID"])
            ->condition("p.published", 1)
            ->groupBy("p.ID")
            ->orderBy("b.order_time DESC")
            ->limit(10);
    }

    public function getListPlace(): string
    {
        return "recent_items";
    }
}
