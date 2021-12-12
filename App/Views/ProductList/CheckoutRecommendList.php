<?php

namespace App\Views\ProductList;

use App\Entity\Basket\Basket;
use App\Entity\Basket\BasketProduct;
use App\Entity\Product\Product;
use CoreDB\Kernel\Database\SelectQueryPreparerAbstract;
use Src\Entity\Cache;
use Src\Entity\Translation;

class CheckoutRecommendList extends SwiperProductList
{

    public function __construct()
    {
        parent::__construct();
        \CoreDB::database()->delete(Cache::getTableName())
        ->condition("key", $this->getListId())->execute();
    }

    public function getListId(): string
    {
        return "recommend_list_" . \CoreDB::currentUser()->ID;
    }

    public function getTitle()
    {
        return Translation::getTranslation("have_you_forgotten");
    }

    public function getQuery(): SelectQueryPreparerAbstract
    {
        $user = \CoreDB::currentUser();
        $basket = Basket::getUserBasket();
        
        $subQuery = \CoreDB::database()->select(Basket::getTableName(), "b2")
        ->join(BasketProduct::getTableName(), "bp2", "b2.ID = bp2.basket")
        ->condition("b2.user", $user->ID->getValue())
        ->condition("b2.is_ordered", 1)
        ->condition("b2.ID", $basket->ID->getValue(), "!=")
        ->select("b2", ["ID"])
        ->groupBy("b2.ID");

        $subQuery2 = \CoreDB::database()->select(Basket::getTableName(), "b4")
        ->join(BasketProduct::getTableName(), "bp4", "b4.ID = bp4.basket")
        ->condition("b4.ID", $basket->ID->getValue())
        ->select("bp4", ["product"]);

        return \CoreDB::database()->select(Basket::getTableName(), "b")
        ->join(BasketProduct::getTableName(), "bp", "b.ID = bp.basket")
        ->join(Product::getTableName(), "p", "bp.product = p.ID")
        ->condition("b.ID", $subQuery, "IN")
        ->condition("bp.product", $subQuery2, "NOT IN")
        ->select("bp", ["product"])
        ->orderBy("b.order_time DESC, p.stockcode")
        ->distinct()
        ->limit(10);
    }

    public function getListPlace(): string
    {
        return "have_you_forgotten";
    }
}
