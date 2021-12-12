<?php

namespace App\Controller\Admin\Banner;

use App\Controller\Admin\BannerController;
use App\Entity\Banner;
use CoreDB\Kernel\Router;
use Src\Controller\NotFoundController;
use Src\Entity\Translation;
use Src\Form\InsertForm;

class InsertController extends BannerController
{

    public ?Banner $banner;
    public InsertForm $bannerInsertForm;

    public function preprocessPage()
    {
        if (isset($this->arguments[0]) && $this->arguments[0]) {
            $this->banner = Banner::get($this->arguments[0]);
            if (!$this->banner) {
                Router::getInstance()->route(NotFoundController::getUrl());
            }
            $title = Translation::getTranslation("edit") . " | " . $this->banner->title;
        } else {
            $this->banner = new Banner();
            $title = Translation::getTranslation("add") . " | " . Translation::getTranslation("Banners");
        }
        $this->setTitle($title);
        $this->bannerInsertForm = $this->banner->getForm();
        $this->bannerInsertForm->processForm();
        $this->bannerInsertForm->addClass("p-3");

        $this->addJsFiles("dist/select/select.js");
        $this->addCssFiles("dist/select/select.css");
    }

    public function echoContent()
    {
        return $this->bannerInsertForm;
    }
}
