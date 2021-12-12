<?php

namespace App\Controller\Admin;

use App\AdminTheme\EcommerceAdminTheme;
use App\Controller\Admin\Productlists\InsertController;
use App\Entity\Product\ProductDiscountList;
use App\Entity\Product\ProductList;
use Src\Form\TreeForm;
use CoreDB\Kernel\Messenger;
use Src\Entity\Translation;
use Src\Views\NavItem;
use Src\Views\TextElement;
use Src\Views\ViewGroup;

class ProductlistsController extends EcommerceAdminTheme
{
    public ViewGroup $tabs;
    public ?TreeForm $productListForm = null;

    public function getTemplateFile(): string
    {
        return "page-admin-productlists.twig";
    }

    public function preprocessPage()
    {
        $listClass = ProductList::getClassByListName(@$this->arguments[0]);
        if (!$listClass) {
            \CoreDB::goTo($this->getUrl() . ProductDiscountList::getTargetList());
        }
        $this->setTitle(Translation::getTranslation("product_lists"));
        if ($listClass) {
            $this->productListForm = new TreeForm(
                $listClass,
                InsertController::getUrl() . @$this->arguments[0] . "/"
            );
            $this->productListForm->setShowEditUrl(true);
            $this->productListForm->processForm();
            $this->productListForm->addClass("col-12");
        }

        $this->tabs = ViewGroup::create("ul", "nav nav-pills p-2");
        $lists = [ProductList::LIST_DISCOUNT];
        foreach ($lists as $list) {
            $this->tabs->addField(
                NavItem::create(
                    "",
                    TextElement::create(
                        Translation::getTranslation($list)
                    )->addClass(
                        @$this->arguments[0] == $list ? "text-white" : ""
                    ),
                    self::getUrl() . $list,
                    @$this->arguments[0] == $list
                )
            );
        }
    }

    public function echoContent()
    {
        return $this->productListForm;
    }
}
