<?php

namespace App\Controller\Admin;

use App\AdminTheme\EcommerceAdminController;
use App\Controller\Admin\Banner\InsertController;
use App\Entity\Banner;
use Src\Entity\Translation;
use Src\Form\TreeForm;

class BannerController extends EcommerceAdminController
{

    public TreeForm $bannerTreeForm;

    public function getTemplateFile(): string
    {
        return "page.twig";
    }

    public function preprocessPage()
    {
        $this->setTitle(Translation::getTranslation("Banners"));
        $this->bannerTreeForm = new TreeForm(
            Banner::class,
            InsertController::getUrl()
        );
        $this->bannerTreeForm->setShowEditUrl(true);
        $this->bannerTreeForm->processForm();
        $this->bannerTreeForm->addClass("col-12");
    }

    public function echoContent()
    {
        return $this->bannerTreeForm;
    }
}
