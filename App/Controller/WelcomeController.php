<?php

namespace App\Controller;

use App\Theme\CustomTheme;
use Src\Entity\Translation;

class WelcomeController extends CustomTheme
{

    public function checkAccess(): bool
    {
        if (\CoreDB::currentUser()->email_verified->getValue()) {
            \CoreDB::goTo(MainpageController::getUrl());
        } else {
            return \CoreDB::currentUser()->isLoggedIn();
        }
    }

    public function buildSidebar()
    {
    }

    public function preprocessPage()
    {
        $this->setTitle(Translation::getTranslation("welcome"));
    }

    public function echoContent()
    {
        return Translation::getTranslation("after_register_welcome_message", [
            \CoreDB::currentUser()->email->getValue()
        ]);
    }

    protected function addDefaultJsFiles()
    {
        parent::addDefaultJsFiles();
        $this->addJsFiles("dist/welcome_page/welcome_page.js");
        $currentUser = \CoreDB::currentUser();
        $this->addJsCode("var userMail = '{$currentUser->email}'");
    }

    protected function addDefaultTranslations()
    {
        parent::addDefaultTranslations();
        $this->addFrontendTranslation("email");
        $this->addFrontendTranslation("send_mail");
    }
}
