<?php

namespace App\Queries;

use App\Entity\Product\PrivateProductOwner;
use CoreDB\Kernel\Database\SelectQueryPreparerAbstract;

class PrivateProductsQuery extends ProductsQuery
{
    public static function getInstance()
    {
        return parent::getByKey("private_products_list");
    }

    public function getResultQuery(): SelectQueryPreparerAbstract
    {
        $query = parent::getResultQuery();
        $query->join(PrivateProductOwner::getTableName(), "ppo", "ppo.product = products.ID");
        $query->condition("ppo.owner", \CoreDB::currentUser()->ID->getValue())
        ->groupBy("products.ID");
        return $query;
    }
}
