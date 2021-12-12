<?php

namespace App\Controller;

use App\Form\RegisterForm;
use Src\Entity\Translation;

class RegisterController extends LoginController
{
    public function __construct($arguments)
    {
        parent::__construct($arguments);
        $this->body_classes = ["bg-gradient-primary"];
        $this->setTitle(Translation::getTranslation("register"));
    }
    public function getTemplateFile(): string
    {
        return "page-register.twig";
    }

    public function checkAccess(): bool
    {
        return true;
    }

    public function preprocessPage()
    {
        if (\CoreDB::currentUser()->isLoggedIn()) {
            \CoreDB::goTo(BASE_URL);
        }
        $this->form = new RegisterForm();
        $this->form->processForm();
    }

    public function echoContent()
    {
        return $this->form;
    }
}
