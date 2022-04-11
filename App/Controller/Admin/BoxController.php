<?php

namespace App\Controller\Admin;

use App\AdminTheme\EcommerceAdminController;
use App\Entity\View\MainpageBox;
use CoreDB\Kernel\Router;
use Src\Controller\NotFoundController;
use Src\Entity\Translation;
use Src\Form\InsertForm;
use Src\Form\TreeForm;

class BoxController extends EcommerceAdminController
{
    public TreeForm $treeForm;
    public MainpageBox $box;
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
                    $this->box = new MainpageBox();
                    break;
                default:
                    $this->box = MainpageBox::get($this->arguments[0]);
            }
            if ($this->box) {
                $this->insertForm = $this->box->getForm();
                $this->insertForm->processForm();
                $this->setTitle(
                    Translation::getTranslation("mainpage_box") . " | " .
                    (
                        $this->box ? $this->box->title :
                        Translation::getTranslation("add")
                    )
                );
            } else {
                Router::getInstance()->route(NotFoundController::getUrl());
            }
        } else {
            $this->setTitle(Translation::getTranslation("mainpage_box"));
            $this->treeForm = new TreeForm(
                MainpageBox::class,
                BoxController::getUrl() . "add"
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
