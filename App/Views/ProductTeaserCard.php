<?php

namespace App\Views;

use App\Controller\ProductsController;
use App\Entity\AppUser;
use App\Entity\Search\SearchApi;
use CoreDB;
use CoreDB\Kernel\Messenger;
use Src\Entity\Cache;
use Src\Entity\Translation;
use Src\Entity\Variable;
use Src\Theme\ResultsViewer;
use Src\Views\TextElement;

class ProductTeaserCard extends ResultsViewer
{
    public $listOptionField = "product_card_list_option";
    public $listOption = 'card';
    public bool $logged_in;
    public bool $non_login_order;
    public function __construct()
    {
        $this->addClass("row");
        $controller = CoreDB::controller();
        $controller->addJsFiles("ecommerce_theme/src/components/product-teaser/product-teaser.js");
        $controller->addCssFiles("ecommerce_theme/src/components/product-teaser/product-teaser.css");
        $controller->addJsFiles("ecommerce_theme/lib/swiper/swiper-bundle.min.js");
        $controller->addCssFiles("ecommerce_theme/lib/swiper/swiper-bundle.min.css");
        $controller->addJsFiles("ecommerce_theme/src/components/swiper/swiper.js");
        $this->listOption = \CoreDB::currentUser()->{$this->listOptionField}->getValue();
        $this->logged_in = \CoreDB::currentUser()->isLoggedIn();
        $this->non_login_order = Variable::getByKey("non_login_order")->value->getValue() == 1;
        if (!$this->logged_in) {
            \CoreDB::controller()->addFrontendTranslation("login");
        }
    }

    public function setData(array $data)
    {
        if (empty($data) && @$_GET["search"]) {
            $cache = Cache::getByBundleAndKey("search_suggestions", $_GET["search"]);
            if (!$cache) {
                $searchSuggestions = SearchApi::getSearchResultByLevenstein($_GET["search"], false);
                Cache::set("search_suggestions", $_GET["search"], json_encode(
                    array_keys($searchSuggestions)
                ));
                $cache = Cache::getByBundleAndKey("search_suggestions", $_GET["search"]);
            }
            $suggestions = [];
            foreach (json_decode($cache->value) as $suggestion) {
                $searchUrl = ProductsController::getUrl() . "?search=" . $suggestion;
                $suggestions[] =
                "<a href='$searchUrl' class='text-info'>$suggestion</a>";
            }
            $message = Translation::getTranslation(
                "search_result_empty",
                [implode(", ", $suggestions)]
            );
            \CoreDB::controller()->createMessage(
                TextElement::create(
                    $message
                )->setIsRaw(true),
                Messenger::INFO
            );
        }
        return parent::setData($data);
    }

    public function getTemplateFile(): string
    {
        if ($this->listOption == AppUser::PRODUCT_CARD_LIST_OPTION_LIST) {
            return "product-teaser-card-list.twig";
        } else {
            return "product-teaser-card.twig";
        }
    }
}
