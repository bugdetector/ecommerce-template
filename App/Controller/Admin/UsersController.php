<?php

namespace App\Controller\Admin;

use App\AdminTheme\EcommerceAdminController;
use App\Entity\AppUser;
use Src\Entity\Translation;
use Src\Form\SearchForm;

class UsersController extends EcommerceAdminController
{
    public SearchForm $searchForm;
    public array $actions;

    public function preprocessPage()
    {
        $this->setTitle(Translation::getTranslation("users"));
        $user = new AppUser();
        $this->searchForm = SearchForm::createByObject($user);
        $this->searchForm->addClass("p-3");
        $this->actions = $user->actions();
        $this->addJsFiles("ecommerce_theme/src/components/user-delete/user-delete.js");
    }

    public function echoContent()
    {
        return $this->searchForm;
    }
}
