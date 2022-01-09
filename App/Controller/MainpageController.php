<?php

namespace App\Controller;

use App\Entity\Banner;
use App\Entity\View\MainpageBox;
use App\Theme\CustomTheme;
use App\Views\MainpageBoxes;
use App\Views\ProductList\FavoritesList;
use App\Views\ProductList\LatestOffers;
use App\Views\ProductList\RecentItems;
use App\Views\ProductList\TopSellers;
use App\Views\SpaceUnderBanner;
use Src\Entity\Translation;
use Src\Entity\User;
use Src\Entity\Variable;

class MainpageController extends CustomTheme
{

    public User $user;
    public array $banners;
    public int $bannerSlideSpeed;
    public ?SpaceUnderBanner $space = null;
    public TopSellers $topSellers;
    public LatestOffers $latestOffers;
    public ?FavoritesList $favorites;
    public ?RecentItems $recentItems;
    public MainpageBoxes $boxesUnderLatest;
    public MainpageBoxes $boxesTopSellers;

    public function checkAccess(): bool
    {
        $currentUser = \CoreDB::currentUser();
        if (!$currentUser->isLoggedIn()) {
            return true;
        } else {
            return parent::checkAccess();
        }
    }

    public function getTemplateFile(): string
    {
        return "page-mainpage.twig";
    }

    public function preprocessPage()
    {
        if (\CoreDB::currentUser()->isLoggedIn()) {
            $this->setTitle(Translation::getTranslation("hello") . ", " . \CoreDB::currentUser()->name);
        } else {
            $this->setTitle(Variable::getByKey("site_name")->value);
        }
        $this->user = \CoreDB::currentUser();
        $this->banners = Banner::getRootElements();
        $speedVariable = Variable::getByKey("banner_slide_speed");
        $this->bannerSlideSpeed = $speedVariable ? $speedVariable->value->getValue() : 3000;
        $this->topSellers = new TopSellers();
        $this->latestOffers = new LatestOffers();
        if (\CoreDB::currentUser()->isLoggedIn()) {
            $this->space = new SpaceUnderBanner();
            $this->recentItems = new RecentItems();
            $this->favorites = new FavoritesList();
        }
        $this->boxesUnderLatest = new MainpageBoxes(MainpageBox::PLACE_UNDER_LATEST_OFFERS);
        $this->boxesTopSellers = new MainpageBoxes(MainpageBox::PLACE_UNDER_TOP_SELLERS);
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
