<?php

namespace App\Views\ProductList;

use App\Entity\Product\Product;
use App\Views\ProductTeaserCard;
use CoreDB\Kernel\Database\SelectQueryPreparerAbstract;
use Src\Entity\Cache;

abstract class SwiperProductList extends ProductTeaserCard
{
    public $title = "";
    abstract public function getListId(): string;
    abstract public function getTitle();
    abstract public function getQuery(): SelectQueryPreparerAbstract;

    public function getClickUrl()
    {
        return null;
    }

    /**
     * @return string
     * Return tag for product tracker analytics
     */
    public function getListPlace(): string
    {
        return $this->getListId();
    }

    public function __construct($executeQuery = true)
    {
        parent::__construct();
        $this->title = $this->getTitle();
        $controller = \CoreDB::controller();
        $controller->addJsFiles("ecommerce_theme/lib/swiper/swiper-bundle.min.js");
        $controller->addCssFiles("ecommerce_theme/lib/swiper/swiper-bundle.min.css");
        $controller->addJsFiles("ecommerce_theme/src/components/swiper/swiper.js");
        if ($executeQuery) {
            $this->setData(array_map(function ($id) {
                return Product::get($id);
            }, $this->getProductIds()));
        }
    }

    public function getProductIds($limit = null)
    {
        $cacheKey = $this->getListId() . ($limit !== null ? "limit_$limit" : "");
        $cache = Cache::getByBundleAndKey("swiper_product_list", $cacheKey);
        if (!$cache) {
            $query = $this->getQuery();
            if ($limit !== null) {
                $query->limit($limit);
            }
            $productIds = $query->execute()->fetchAll(\PDO::FETCH_COLUMN);
            Cache::set("swiper_product_list", $cacheKey, json_encode($productIds));
        } else {
            $productIds = json_decode($cache->value->getValue());
        }
        return $productIds;
    }

    public function getTemplateFile(): string
    {
        return "swiper_product_list.twig";
    }

    public function isLoggedIn()
    {
        return \CoreDB::currentUser()->isLoggedIn();
    }
}
