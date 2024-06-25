<?php

namespace App\Controller\Admin;

use App\AdminTheme\EcommerceAdminController;
use Src\Entity\Translation;
use Src\Views\AlertMessage;

class IssuesController extends EcommerceAdminController
{
    public $searchForm;

    public function preprocessPage()
    {
        $this->setTitle(Translation::getTranslation("todo"));
    }

    public function echoContent()
    {
        if (defined("FOLLOWUP_URL") && FOLLOWUP_URL) {
            return "<iframe 
                src='" . FOLLOWUP_URL . "' 
                class='w-100'
                style='height: calc(100vh - 70px);'
                frameBorder='0'></iframe>";
        } else {
            return AlertMessage::create(
                Translation::getTranslation("no_follow_url_defined"),
                AlertMessage::MESSAGE_TYPE_WARNING
            );
        }
    }
}
