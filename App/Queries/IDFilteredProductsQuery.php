<?php

namespace App\Queries;

use CoreDB\Kernel\Database\SelectQueryPreparerAbstract;

class IDFilteredProductsQuery extends ProductsQuery
{
    public ?array $idList = null;

    public function setIdList(array $idList)
    {
        $this->idList = $idList;
    }

    public function getResultQuery(): SelectQueryPreparerAbstract
    {
        $query = parent::getResultQuery();
        if ($this->idList) {
            $query->condition("products.ID", $this->idList, "IN");
            $orderBy = "FIND_IN_SET(products.ID,'" . implode(",", $this->idList) . "')";
            $query->orderBy($orderBy);
        }
        return $query;
    }
}
