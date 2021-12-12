<?php

namespace App\Controller;

use App\Entity\Search\SearchApi;
use CoreDB\Kernel\ServiceController;

class ApiController extends ServiceController
{

    /**
     * @inheritdoc
     */
    public function checkAccess(): bool
    {
        return boolval($this->method);
    }

    public function search()
    {
        $search = $_GET["search"];
        return $search ? SearchApi::getSearchResult($search) : [];
    }
}
