<?php

namespace App\Form;

use Src\Entity\Translation;
use Src\Entity\User;
use Src\Entity\Watchdog;
use Src\Form\LoginForm as FormLoginForm;

class LoginForm extends FormLoginForm
{
    public function validate(): bool
    {
        $user = User::getUserByUsername($this->request["username"]) ?:
            User::getUserByEmail($this->request["username"]);
        if ($user && $user->password->getValue() == sha1($this->request["password"])) {
            $user->map([
                "password" => $this->request["password"]
            ]);
            $user->save();
            Watchdog::log("password_rehashed", Translation::getTranslation(
                "Password rehashed for user %s",
                [$user->username->getValue()]
            ));
        }
        $parentValid = parent::validate();
        if ($parentValid) {
            if (!$user->email_verified->getValue()) {
                \CoreDB::messenger()->createMessage(Translation::getTranslation("email_verify_error"));
            }
        }
        return empty($this->errors);
    }
}
