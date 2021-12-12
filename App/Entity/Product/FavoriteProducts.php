<?php

namespace App\Entity\Product;

use CoreDB;
use CoreDB\Kernel\Model;
use CoreDB\Kernel\Database\DataType\TableReference;

/**
 * Object relation with table favorite_products
 * @author murat
 */

class FavoriteProducts extends Model
{
    /**
    * @var TableReference $product
    * Product whic is favorite.
    */
    public TableReference $product;
    /**
    * @var TableReference $user
    * User whose is favorited.
    */
    public TableReference $user;

    /**
     * @inheritdoc
     */
    public static function getTableName(): string
    {
        return "favorite_products";
    }


    public static function isProductInFavorite(int $productId): bool
    {
        return FavoriteProducts::getFavoriteRecord($productId) ? true : false;
    }

    public static function getFavoriteRecord($productId): ?FavoriteProducts
    {
        return FavoriteProducts::get([
            "product" => $productId,
            "user" => CoreDB::currentUser()->ID->getValue()
        ]);
    }

    public static function toggleFavorite(Product $product): bool
    {
        $favorite = FavoriteProducts::getFavoriteRecord($product->ID->getValue());
        if ($favorite) {
            $favorite->delete();
            return false;
        } else {
            $favorite = new FavoriteProducts();
            $favorite->product->setValue($product->ID->getValue());
            $favorite->user->setValue(CoreDB::currentUser()->ID);
            $favorite->save();
            return true;
        }
    }
}
