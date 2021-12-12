<?php

namespace App\Controller;

use App\Queries\MyOrdersQuery;
use App\Theme\CustomTheme;
use Src\Entity\Translation;
use Src\Form\SearchForm;

class MyordersController extends CustomTheme
{
    public $searchForm;

    public function preprocessPage()
    {
        $this->setTitle(Translation::getTranslation("my_orders"));
        $this->searchForm = SearchForm::createByObject(MyOrdersQuery::getInstance());
        $this->searchForm->addClass("p-3");
    }

    public function echoContent()
    {
        return $this->searchForm;
    }
}
