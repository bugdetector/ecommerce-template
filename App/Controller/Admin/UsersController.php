<?php

namespace App\Controller\Admin;

use App\AdminTheme\EcommerceAdminTheme;
use App\Entity\CustomUser;
use Src\Entity\Translation;
use Src\Form\SearchForm;

class UsersController extends EcommerceAdminTheme
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
    }

    public function getTemplateFile(): string
    {
        return "page-admin-products.twig";
    }

    public function echoContent()
    {
        return $this->searchForm;
    }

    protected function addDefaultJsFiles()
    {
        parent::addDefaultJsFiles();
        $this->addJsFiles("dist/user-delete/user-delete.js");
    }
}
