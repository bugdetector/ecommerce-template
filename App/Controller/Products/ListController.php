<?php

namespace App\Controller\Products;

use App\Controller\ProductsController;
use App\Entity\Banner;
use App\Form\ProductSearchForm;
use App\Queries\IDFilteredProductsQuery;
use App\Views\ProductList\LatestOffers;
use App\Views\ProductList\RecentItems;
use App\Views\ProductList\SwiperProductList;
use App\Views\ProductList\TopSellers;
use CoreDB\Kernel\Router;
use Src\Controller\NotFoundController;
use Src\Entity\Translation;

class ListController extends ProductsController
{

    protected ?Banner $banner;
    protected ?array $idList = null;
    protected ?IDFilteredProductsQuery $query;
    protected ?SwiperProductList $swiperList = null;

    public function preprocessPage()
    {
        switch (@$this->arguments[0]) {
            case "banner":
                $this->banner = Banner::get(@$this->arguments[1]);
                if (!$this->banner) {
                    Router::getInstance()->route(NotFoundController::getUrl());
                }
                $this->setTitle($this->banner->title);
                $this->idList = array_map(function ($el) {
                    return $el["product"];
                }, $this->banner->banner_product->getValue());
                break;
            case "latest_offers":
                $this->swiperList = new LatestOffers(false);
                break;
            case "recent_items":
                $this->swiperList = new RecentItems(false);
                break;
            case "top_sellers":
                $this->swiperList = new TopSellers(false);
                break;
            default:
                Router::getInstance()->route(NotFoundController::getUrl());
        }
        $this->query = IDFilteredProductsQuery::getInstance();
        if ($this->idList) {
            $this->query->setIdList($this->idList);
        } elseif ($this->swiperList) {
            $this->setTitle($this->swiperList->getTitle());
            $this->query->setIdList($this->swiperList->getProductIds(0));
        }
        $this->productListSearch = ProductSearchForm::createByObject(
            $this->query
        );
        if ($this->swiperList) {
            $this->productListSearch->setListPlace($this->swiperList->getListPlace());
        }
    }
}
