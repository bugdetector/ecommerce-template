<?php

namespace App\Controller;

use App\Theme\CustomTheme;
use Src\Entity\Translation;
use Src\Entity\User;

class VerifyController extends CustomTheme
{

    public $pageMessage = "";

    public function checkAccess(): bool
    {
        $user = \CoreDB::currentUser();
        if ($user->email_verified->getValue()) {
            \CoreDB::goTo(MainpageController::getUrl());
        } else {
            return true;
        }
    }

    public function buildSidebar()
    {
    }

    public function preprocessPage()
    {
        $user = User::get(@$this->arguments[0]);
        if ($user && $user->email_verification_key == @$this->arguments[1]) {
            $user->email_verified->setValue(1);
            $user->email_verification_key->setValue("");
            $user->save();
            $this->pageMessage = Translation::getTranslation("email_verify_success");
            $this->setTitle(Translation::getTranslation("email_verification"));
            header("Refresh:3; url=" . BASE_URL);
        } elseif (@$this->arguments[0] && @$this->arguments[1]) {
            $this->createMessage(
                Translation::getTranslation("link_used")
            );
            $this->setTitle(Translation::getTranslation("error"));
            return;
        }
        if (!$user) {
            $user = \CoreDB::currentUser();
        }
    }

    public function echoContent()
    {
        return $this->pageMessage;
    }
}
