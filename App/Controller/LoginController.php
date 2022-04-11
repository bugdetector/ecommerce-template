<?php

namespace App\Controller;

use App\Form\LoginForm;
use App\Theme\AppController;
use CoreDB\Kernel\ConfigurationManager;
use Src\Entity\Translation;
use Src\Entity\User;

class LoginController extends AppController
{
    public $form;
    public ?User $loginAsUser = null;
    public function __construct($arguments)
    {
        parent::__construct($arguments);
        $this->setTitle(Translation::getTranslation("welcome") . "!");
        if (isset($_GET["login_as_user"])) {
            $userClass = ConfigurationManager::getInstance()->getEntityInfo("users")["class"];
            $this->loginAsUser = $userClass::get($_GET["login_as_user"]);
        }
    }
    public function getTemplateFile(): string
    {
        return "page-login.twig";
    }

    public function checkAccess(): bool
    {
        return true;
    }

    public function preprocessPage()
    {
        if ($this->loginAsUser) {
            if ($this->loginAsUser->isAdmin()) {
                $this->createMessage(
                    Translation::getTranslation("cannot_login_as_another_admin_user")
                );
                \CoreDB::goTo(
                    @$_SERVER["HTTP_REFERER"] ?: BASE_URL
                );
            }
            $_SESSION[BASE_URL . "-BACKUP-UID"] = \CoreDB::currentUser()->ID;
            $_SESSION[BASE_URL . "-UID"] = $this->loginAsUser->ID;
            \CoreDB::goTo(BASE_URL);
        } else {
            if (\CoreDB::currentUser()->isLoggedIn()) {
                \CoreDB::goTo(BASE_URL);
            }
            $this->form = new LoginForm();
            $this->form->processForm();
        }
    }

    public function echoContent()
    {
        return $this->form;
    }
}
