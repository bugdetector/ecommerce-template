<?php

namespace App\Controller\Admin;

use App\AdminTheme\EcommerceAdminController;
use App\Entity\CustomUser;
use Src\Entity\Translation;
use Src\Form\SearchForm;

class UsersController extends EcommerceAdminController
{

    public SearchForm $searchForm;
    public array $actions;

    public function preprocessPage()
    {
        $this->setTitle(Translation::getTranslation("users"));
        $customUser = new CustomUser();
        $this->searchForm = SearchForm::createByObject($customUser);
        $this->searchForm->addClass("p-3");
        $this->actions = $customUser->actions();
        $this->addJsFiles("dist/user-delete/user-delete.js");
    }

    public function echoContent()
    {
        return $this->searchForm;
    }
}
