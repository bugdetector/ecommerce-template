<?php

namespace App\Queries;

use App\Entity\Product\FavoriteProducts;
use App\Entity\Product\Product;
use Src\Entity\File;
use Src\Entity\ViewableQueries;

class SpecialProductsQuery extends ViewableQueries
{
    public static function getInstance()
    {
        return parent::getByKey("special_products_list");
    }

    public function postProcessRow(&$row): void
    {
        $product = Product::get($row["ID"]);
        $row["product"] = $product;
        $row["favorite"] = FavoriteProducts::isProductInFavorite($row["ID"]);
    }
}
