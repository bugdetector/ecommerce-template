<?php

namespace App\Controller;

use App\Entity\Product\ProductCategory;
use App\Theme\AppController;
use CoreDB\Kernel\Router;
use Src\Controller\NotFoundController;
use Src\Entity\Translation;
use Src\Views\Image;
use Src\Views\Link;
use Src\Views\ViewGroup;

class CatalogueController extends AppController
{
    public $categoryCards;

    public function checkAccess(): bool
    {
        return true;
    }

    public function preprocessPage()
    {
        if (@$this->arguments[0]) {
            /** @var ProductCategory */
            $category = ProductCategory::get($this->arguments[0]);
            if (!$category) {
                Router::getInstance()->route(NotFoundController::getUrl());
            }
            $this->setTitle($category->name);
            $categories = $category->getSubNodes();
        } else {
            $this->setTitle(Translation::getTranslation("catalog"));
            $categories = ProductCategory::getRootElements();
        }
        $this->categoryCards = ViewGroup::create("div", "row p-2");
        /** @var ProductCategory $category */
        foreach ($categories as $category) {
            if ($category->getSubNodes()) {
                $url = CatalogueController::getUrl() . $category->ID;
            } else {
                $url = ProductsController::getUrl() . "?category={$category->ID}";
            }
            $categoryCard = ViewGroup::create("div", "col-sm-6 col-md-4 col-lg-3 p-1");
            $categoryCard->addField(
                Link::create(
                    $url,
                    Image::create(
                        $category->image->getValue() ?
                        $category->getFileUrlForField("image") :
                        BASE_URL . "/assets/awaiting-image.jpg",
                        $category->name,
                        true
                    )->addClass("w-100 my-2")
                    ->addAttribute("title", $category->name)
                )->addClass("card p-3 text-decoration-none fw-bolder")
            );
            $this->categoryCards->addField(
                $categoryCard
            );
        }
    }

    public function echoContent()
    {
        return $this->categoryCards;
    }
}
