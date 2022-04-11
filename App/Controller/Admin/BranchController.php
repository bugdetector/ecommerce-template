<?php

namespace App\Controller\Admin;

use App\AdminTheme\EcommerceAdminController;
use App\Controller\Admin\Branch\InsertController;
use App\Entity\Branch;
use Src\Entity\Translation;
use Src\Form\Form;
use Src\Form\TreeForm;

class BranchController extends EcommerceAdminController
{

    /** @var TreeForm */
    public Form $branchForm;


    public function preprocessPage()
    {
        $this->setTitle(Translation::getTranslation("branch"));
        $this->branchForm = new TreeForm(Branch::class, InsertController::getUrl());
        $this->branchForm->setShowEditUrl(true);
        $this->branchForm->processForm();
    }

    public function getTemplateFile(): string
    {
        return "page.twig";
    }

    public function echoContent()
    {
        return $this->branchForm;
    }
}
