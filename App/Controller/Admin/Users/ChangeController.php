<?php

namespace App\Controller\Admin\Users;

use App\Controller\Admin\UsersController;
use App\Form\ChangeAccountForm;
use Src\Entity\Translation;

class ChangeController extends UsersController
{

    public ChangeAccountForm $changeAccountForm;

    public function preprocessPage()
    {
        $this->setTitle(Translation::getTranslation("change_account_number"));
        $this->changeAccountForm = new ChangeAccountForm();
        $this->changeAccountForm->processForm();
    }

    public function echoContent()
    {
        return $this->changeAccountForm;
    }
}
