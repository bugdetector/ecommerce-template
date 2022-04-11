<?php

namespace App\Controller\Admin;

use App\Controller\AdminController;
use App\Entity\Blog;
use Src\Entity\Translation;
use Src\Form\SearchForm;

class BlogController extends AdminController
{

    public $searchForm;

    public function preprocessPage()
    {
        $this->setTitle(Translation::getTranslation("blog"));
        $blog = new Blog();
        $this->searchForm = SearchForm::createByObject($blog);
        $this->searchForm->addClass("p-3");
        $this->actions = $blog->actions();
    }

    public function echoContent()
    {
        return $this->searchForm;
    }
}
