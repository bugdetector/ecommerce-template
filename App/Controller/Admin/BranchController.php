<?php

namespace App\Controller\Admin;

use App\AdminTheme\EcommerceAdminTheme;
use App\Controller\Admin\Branch\InsertController;
use App\Entity\Branch;
use Src\Form\Form;
use Src\Form\TreeForm;

class BranchController extends EcommerceAdminTheme
{

    /** @var TreeForm */
    public Form $branchForm;


    public function preprocessPage()
    {
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
