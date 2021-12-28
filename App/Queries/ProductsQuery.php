<?php

namespace App\Queries;

use App\Entity\Basket\Basket;
use App\Entity\Basket\BasketProduct;
use App\Entity\Product\FavoriteProducts;
use App\Entity\Product\Product;
use App\Entity\Product\ProductCategory;
use App\Entity\Product\ProductPrice;
use App\Entity\Search\SearchApi;
use CoreDB\Kernel\Database\QueryCondition;
use CoreDB\Kernel\Database\SelectQueryPreparerAbstract;
use Src\Entity\ViewableQueries;

class ProductsQuery extends ViewableQueries
{
    public static function getInstance()
    {
        return parent::getByKey("products_list");
    }

    public function getSearchFormFields(bool $translateLabel = true): array
    {
        $searchFormFields = parent::getSearchFormFields($translateLabel);
        unset($searchFormFields["ID"]);
        return $searchFormFields;
    }

    public function postProcessRow(&$row): void
    {
        $row["favorite"] = FavoriteProducts::isProductInFavorite($row["ID"]);
        /** @var Product */
        $product = Product::get($row["ID"]);
        $row["product"] = $product;
        $row["promoted"] = $product->getProductListEntry();
        $row["prices"] = $product->getPrices();
        $row["on_offer"] = false;
        foreach ($row["prices"] as $price) {
            if ($price["list_price"] != $price["offer"]) {
                $row["on_offer"] = true;
            }
        }
    }

    public function getResultQuery(): SelectQueryPreparerAbstract
    {
        $query = parent::getResultQuery();
        /** @var Basket */
        $userBasket = Basket::getUserBasket();
        $query->condition("products.published", 1);
        $query->join(ProductPrice::getTableName(), "pp", "products.ID = pp.product AND pp.price > 0");
        $query->groupBy("products.ID");
        $query->orderBy("products.weight DESC");
        $query->leftjoin(
            Basket::getTableName(),
            "basket",
            "basket.is_ordered = 0 AND basket.ID = " . $userBasket->ID->getValue()
        )->leftjoin(BasketProduct::getTableName(), "bp", "basket.ID = bp.basket AND  products.ID = bp.product")
        ->select("bp", ["quantity"]);
        if (isset($_GET["search"]) && $_GET["search"]) {
            SearchApi::set($_GET["search"]);
            $category = ProductCategory::get(["name" => $_GET["search"]]);
            if ($category) {
                $_GET["category"] = $category->ID->getValue();
            } else {
                $searchWords = explode(" ", $_GET["search"]);
                foreach ($searchWords as $word) {
                    $word = "%{$word}%";
                    $searchCondition = \CoreDB::database()
                    ->condition($query)
                    ->condition("title", $word, "LIKE", "OR")
                    ->condition("stockcode", $word, "LIKE", "OR");
                    $query->condition($searchCondition);
                }
            }
        }
        if (isset($_GET["category"]) && $_GET["category"]) {
            if (!isset($category)) {
                $category = ProductCategory::get($_GET["category"]);
            }
            if ($category) {
                \CoreDB::controller()->setTitle($category->name);
            }
            $query->leftjoin(ProductCategory::getTableName(), "pcat", "pcat.ID = category");
            $categoryCondition = new QueryCondition($query);
            $categoryCondition->condition("category", $_GET["category"])
            ->condition("pcat.parent", $_GET["category"], "=", "OR");
            $query->condition($categoryCondition);
        }
        return $query;
    }
}
