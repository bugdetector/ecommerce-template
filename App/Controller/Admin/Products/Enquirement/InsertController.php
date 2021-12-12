<?php

namespace App\Controller\Admin\Products\Enquirement;

use App\Controller\Admin\Products\EnquirementController;
use App\Entity\Product\Enquirement;
use CoreDB\Kernel\Router;
use Src\Controller\NotFoundController;
use Src\Entity\Translation;
use Src\Form\InsertForm;

class InsertController extends EnquirementController
{
    public ?Enquirement $enquirement;
    public InsertForm $enquirementInsertForm;

    public function preprocessPage()
    {
        if (isset($this->arguments[0]) && $this->arguments[0]) {
            $this->enquirement = Enquirement::get($this->arguments[0]);
            if (!$this->enquirement) {
                Router::getInstance()->route(NotFoundController::getUrl());
            }
            $title = Translation::getTranslation("edit") . " | " . $this->enquirement->ID;
        } else {
            $this->enquirement = new Enquirement();
            $title = Translation::getTranslation("add");
        }
        $this->setTitle($title);
        $this->enquirementInsertForm = $this->enquirement->getForm();
        $this->enquirementInsertForm->processForm();
        $this->enquirementInsertForm->addClass("p-3");
        $this->actions = $this->enquirement->actions();
    }

    public function echoContent()
    {
        return $this->enquirementInsertForm;
    }
}
