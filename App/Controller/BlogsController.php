<?php

namespace App\Controller;

use App\Form\ProductSearchForm;
use App\Queries\BlogQuery;
use App\Theme\CustomTheme;
use Src\Entity\Translation;
use Src\Form\SearchForm;

class BlogsController extends CustomTheme
{


    public SearchForm $searchForm;

    public function checkAccess(): bool
    {
        $currentUser = \CoreDB::currentUser();
        if (!$currentUser->isLoggedIn()) {
            return true;
        } else {
            return parent::checkAccess();
        }
    }

    public function preprocessPage()
    {
        $this->setTitle(Translation::getTranslation("blog"));
        $this->searchForm = ProductSearchForm::createByObject(BlogQuery::getInstance());
    }

    public function echoContent()
    {
        return $this->searchForm;
    }
}
