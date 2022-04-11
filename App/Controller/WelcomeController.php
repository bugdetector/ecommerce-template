<?php

namespace App\Controller;

use App\Theme\AppController;
use Src\Entity\Translation;

class WelcomeController extends AppController
{

    public function checkAccess(): bool
    {
        if (\CoreDB::currentUser()->email_verified->getValue()) {
            \CoreDB::goTo(MainpageController::getUrl());
        } else {
            return \CoreDB::currentUser()->isLoggedIn();
        }
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
        $this->addJsFiles("dist/welcome_page/welcome_page.js");
        $currentUser = \CoreDB::currentUser();
        $this->addJsCode("var userMail = '{$currentUser->email}'");
        $this->addFrontendTranslation("email");
        $this->addFrontendTranslation("send_mail");
    }
}
