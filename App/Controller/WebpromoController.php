<?php

namespace App\Controller;

use App\Controller\Products\ListController;
use App\Entity\Banner;
use App\Form\ProductSearchForm;
use App\Queries\IDFilteredProductsQuery;
use App\Views\ProductList\LatestOffers;

class WebpromoController extends ListController
{
    public array $banners;
    public function getTemplateFile(): string
    {
        return "page-web_promo.twig";
    }
    public function preprocessPage()
    {
        $this->banners = Banner::getRootElements();
        $this->swiperList = new LatestOffers();
        $this->query = IDFilteredProductsQuery::getInstance();
        if ($this->idList) {
            $this->query->setIdList($this->idList);
        } elseif ($this->swiperList) {
            $this->setTitle($this->swiperList->getTitle());
            $this->query->setIdList($this->swiperList->getProductIds(20));
        }
        $this->productListSearch = ProductSearchForm::createByObject(
            $this->query
        );
    }
    protected function addDefaultJsFiles()
    {
        parent::addDefaultJsFiles();
        $this->addJsFiles("dist/mainpage/mainpage.js");
    }

    protected function addDefaultCssFiles()
    {
        parent::addDefaultCssFiles();
        $this->addCssFiles("dist/mainpage/mainpage.css");
    }
}
