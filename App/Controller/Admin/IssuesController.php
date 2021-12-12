<?php

namespace App\Controller\Admin;

use App\AdminTheme\EcommerceAdminTheme;
use Src\Entity\Translation;

class IssuesController extends EcommerceAdminTheme
{

    public $searchForm;

    public function preprocessPage()
    {
        $this->setTitle(Translation::getTranslation("todo"));
    }

    public function getTemplateFile(): string
    {
        return "page-todo.twig";
    }

    public function echoContent()
    {
        return "<iframe 
            src='https://followup.ai-websolutions.com/watch/98703513a8d89d5b2a47d6ce1f5f4802' 
            class='w-100'
            style='height: calc(100vh - 70px);'
            frameBorder='0'></iframe>";
    }
}
