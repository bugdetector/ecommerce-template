<?php

namespace App\Controller\Admin\Users;

use App\Controller\Admin\UsersController;
use App\Entity\AppUser;
use CoreDB\Kernel\Router;
use Src\Controller\NotFoundController;
use Src\Entity\Translation;
use Src\Entity\User;
use Src\Form\InsertForm;

class InsertController extends UsersController
{
    public ?User $user;
    public InsertForm $userInsertForm;

    public function preprocessPage()
    {
        if (isset($this->arguments[0]) && $this->arguments[0]) {
            $this->user = AppUser::get($this->arguments[0]);
            if (!$this->user) {
                Router::getInstance()->route(NotFoundController::getUrl());
            }
            $title = Translation::getTranslation("edit") . " | " . $this->user->username;
        } else {
            $this->user = new AppUser();
            $title = Translation::getTranslation("add_new_user");
        }
        $this->setTitle($title);
        $this->userInsertForm = $this->user->getForm();
        $this->userInsertForm->processForm();
        $this->userInsertForm->addClass("p-3");
    }

    public function echoContent()
    {
        return $this->userInsertForm;
    }
}
