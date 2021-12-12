<?php

namespace App\Controller\Admin;

use App\AdminTheme\EcommerceAdminTheme;
use App\Entity\Product\ProductCategory;
use CoreDB\Kernel\Router;
use Src\Controller\NotFoundController;
use Src\Form\TreeForm;
use Src\Entity\Translation;

class CategoryController extends EcommerceAdminTheme
{

    public $categoryForm;

    public function getTemplateFile(): string
    {
        if (@$this->arguments[0]) {
            return "page-admin-products.twig";
        } else {
            return "page.twig";
        }
    }

    public function preprocessPage()
    {
        if (@$this->arguments[0]) {
            if ($this->arguments[0] == "add") {
                $category = new ProductCategory();
            } else {
                $category = ProductCategory::get(@$this->arguments[0]);
            }
            if (!$category) {
                Router::getInstance()->route(NotFoundController::getUrl());
            }
            $this->categoryForm = $category->getForm();
            $this->setTitle($category->name);
        } else {
            $this->setTitle(Translation::getTranslation("categories"));
            $this->categoryForm = new TreeForm(ProductCategory::class, $this->getUrl() . "add");
            $this->categoryForm->setShowEditUrl(true);
        }
        $this->categoryForm->processForm();
        $this->categoryForm->addClass("col-12");
    }

    public function echoContent()
    {
        return $this->categoryForm;
    }
}
