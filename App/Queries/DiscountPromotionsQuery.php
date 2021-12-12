<?php

namespace App\Queries;

use App\Entity\Product\ProductDiscountList;
use App\Views\ProductTeaserCard;
use CoreDB\Kernel\Database\SelectQueryPreparerAbstract;
use Src\Theme\ResultsViewer;

class DiscountPromotionsQuery extends ProductsQuery
{
    public function getResultQuery(): SelectQueryPreparerAbstract
    {
        $query = parent::getResultQuery();
        $query->join(ProductDiscountList::getTableName(), "dl", "dl.product = products.ID")
        ->condition("dl.list", ProductDiscountList::getTargetList())
        ->condition("dl.start_date", date("Y-m-d 00:00:00"), "<=")
        ->condition("dl.end_date", date("Y-m-d 23:59:59"), ">=")
        ->orderBy("dl.weight");
        return $query;
    }

    public function getResultsViewer(): ResultsViewer
    {
        return new ProductTeaserCard();
    }
}
