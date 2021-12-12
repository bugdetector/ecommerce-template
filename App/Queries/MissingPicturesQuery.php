<?php

namespace App\Queries;

use App\Entity\Product\ProductPicture;
use CoreDB\Kernel\Database\SelectQueryPreparerAbstract;

class MissingPicturesQuery extends AdminProductsQuery
{

    public function getResultQuery(): SelectQueryPreparerAbstract
    {
        $query = parent::getResultQuery();
        $query->leftjoin(ProductPicture::getTableName(), 'pp', 'products.ID = pp.product')
        ->condition("pp.ID", null, "IS")
        ->condition("pp.image", null, "IS", "OR")
        ->condition("published", 1);
        return $query;
    }
}
