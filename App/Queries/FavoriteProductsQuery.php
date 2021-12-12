<?php

namespace App\Queries;

use App\Entity\Product\FavoriteProducts;
use App\Views\FavoriteProductTeaserCard;
use CoreDB;
use CoreDB\Kernel\Database\SelectQueryPreparerAbstract;
use Src\Theme\ResultsViewer;

class FavoriteProductsQuery extends ProductsQuery
{
    public function getResultQuery(): SelectQueryPreparerAbstract
    {
        $query = parent::getResultQuery();
        $query->join(FavoriteProducts::getTableName(), "fp", "fp.product = products.ID")
        ->condition("fp.user", CoreDB::currentUser()->ID->getValue());
        return $query;
    }

    public function getPaginationLimit(): int
    {
        return 0;
    }

    public function getResultsViewer(): ResultsViewer
    {
        return new FavoriteProductTeaserCard();
    }
}
