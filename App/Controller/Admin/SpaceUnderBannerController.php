<?php

namespace App\Controller\Admin;

use App\AdminTheme\EcommerceAdminController;
use App\AdminTheme\EcommerceAdminTheme;
use App\Controller\AdminController;
use App\Entity\View\SpaceUnderBanner;
use CoreDB\Kernel\Router;
use Src\Controller\NotFoundController;
use Src\Entity\Translation;
use Src\Form\InsertForm;
use Src\Form\TreeForm;

class SpaceUnderBannerController extends EcommerceAdminController
{
    public TreeForm $treeForm;
    public SpaceUnderBanner $space;
    public ?InsertForm $insertForm = null;

    public function getTemplateFile(): string
    {
        return "page.twig";
    }

    public function preprocessPage()
    {
        if (@$this->arguments[0]) {
            switch ($this->arguments[0]) {
                case "add":
                    $this->space = new SpaceUnderBanner();
                    break;
                default:
                    $this->space = SpaceUnderBanner::get($this->arguments[0]);
            }
            if ($this->space) {
                $this->insertForm = $this->space->getForm();
                $this->insertForm->processForm();
                $this->setTitle(
                    Translation::getTranslation("space_under_banner") . " | " .
                    (
                        $this->space ? $this->space->title :
                        Translation::getTranslation("add")
                    )
                );
            } else {
                Router::getInstance()->route(NotFoundController::getUrl());
            }
        } else {
            $this->setTitle(Translation::getTranslation("space_under_banner"));
            $this->treeForm = new TreeForm(
                SpaceUnderBanner::class,
                AdminController::getUrl() . "space_under_banner/add"
            );
            $this->treeForm->setShowEditUrl(true);
            $this->treeForm->processForm();
            $this->treeForm->addClass("col-12");
        }
    }

    public function echoContent()
    {
        return $this->insertForm ?: $this->treeForm;
    }
}
