<?php

namespace App\Views\ProductList;

use App\Controller\FavoritesController;
use App\Entity\Product\FavoriteProducts;
use CoreDB\Kernel\Database\SelectQueryPreparerAbstract;
use Src\Entity\Cache;
use Src\Entity\Translation;
use Src\Views\Link;

class FavoritesList extends SwiperProductList
{
    public function __construct()
    {
        parent::__construct();
        \CoreDB::database()->delete(Cache::getTableName())
        ->condition("key", $this->getListId())->execute();
    }

    public function getListId(): string
    {
        return "favorites_list_" . \CoreDB::currentUser()->ID;
    }

    public function getTitle()
    {
        return Translation::getTranslation("favorites");
    }

    public function getClickUrl()
    {
        return FavoritesController::getUrl();
    }

    public function getQuery(): SelectQueryPreparerAbstract
    {
        return \CoreDB::database()->select(FavoriteProducts::getTableName(), "fp")
            ->condition("fp.user", \CoreDB::currentUser()->ID->getValue())
            ->select("fp", ["product"])
            ->limit(10);
    }

    public function getListPlace(): string
    {
        return "favorites";
    }
}
