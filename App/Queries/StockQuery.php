<?php

namespace App\Queries;

use App\Entity\Branch;
use App\Entity\Product\Stock;
use CoreDB\Kernel\Database\SelectQueryPreparerAbstract;
use Src\Entity\ViewableQueries;

class StockQuery extends ViewableQueries
{
    public static function getInstance()
    {
        return self::getByKey("stock_query");
    }

    public function getResultHeaders(bool $translateLabel = true): array
    {
        $headers = parent::getResultHeaders($translateLabel);
        $headers['s1q'] = 'Hornsey';
        $headers['s2q'] = 'Leyton';
        $headers['s3q'] = 'New Cross';
        $headers['s4q'] = 'Acton';

        return $headers;
    }

    public function getResultQuery(): SelectQueryPreparerAbstract
    {
        $query = parent::getResultQuery();
        $query->join(Stock::getTableName(), 's1', 's1.branch = 1 AND s1.product = products.ID')
        ->join(Stock::getTableName(), 's2', 's2.branch = 2 AND s2.product = products.ID')
        ->join(Stock::getTableName(), 's3', 's3.branch = 3 AND s3.product = products.ID')
        ->join(Stock::getTableName(), 's4', 's4.branch = 4 AND s4.product = products.ID')
        ->select('s1', ['quantity AS s1q'])
        ->select('s2', ['quantity AS s2q'])
        ->select('s3', ['quantity AS s3q'])
        ->select('s4', ['quantity AS s4q']);
        return $query;
    }
}
