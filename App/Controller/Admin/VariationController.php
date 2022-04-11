<?php

namespace App\Controller\Admin;

use App\AdminTheme\EcommerceAdminController;
use App\Entity\Product\VariationOption;
use Src\Form\TreeForm;
use Src\Entity\Translation;

class VariationController extends EcommerceAdminController
{

    public TreeForm $variationTreeForm;

    public function getTemplateFile(): string
    {
        return "page.twig";
    }

    public function preprocessPage()
    {
        $this->setTitle(Translation::getTranslation("categories"));
        $this->variationTreeForm = new TreeForm(VariationOption::class);
        $this->variationTreeForm->processForm();
        $this->variationTreeForm->addClass("col-12");
    }

    public function echoContent()
    {
        return $this->variationTreeForm;
    }
}
