<?php

namespace App\Form;

use App\Controller\VerifyController;
use App\Controller\WelcomeController;
use CoreDB;
use Exception;
use Src\Entity\DynamicModel;
use Src\Entity\Translation;
use Src\Entity\User;
use Src\Entity\Variable;
use Src\Form\Form;
use Src\Form\Widget\FormWidget;
use Src\Form\Widget\InputWidget;
use Src\Form\Widget\SelectWidget;

class RegisterForm extends Form
{
    public string $method = "POST";

    public function __construct()
    {
        $user = \CoreDB::currentUser();
        parent::__construct();
        $this->addClass("user");
        $this->addField(
            InputWidget::create("company_name")
            ->addAttribute("autofocus", "true")
        );
        $this->addField(
            InputWidget::create("name")
        );
        $this->addField(
            InputWidget::create("surname")
        );
        $this->addField(
            InputWidget::create("email")
                ->setType("email")
        );
        $this->addField(
            InputWidget::create("phone")
                ->setType("tel")
        );
        $this->addField(
            InputWidget::create("address")
        );
        $this->addField(
            InputWidget::create("town")
        );
        $this->addField(
            InputWidget::create("county")
        );
        $this->addField(
            InputWidget::create("postalcode")
        );
        $this->addField(
            InputWidget::create("password")
                ->setType("password")
        );
        $this->addField(
            InputWidget::create("password_again")
                ->setType("password")
        );
        /** @var FormWidget $field */
        foreach ($this->fields as $field) {
            $label = Translation::getTranslation($field->name);
            $field->setLabel($label)
                ->addClass("form-control-user")
                ->addAttribute("placeholder", $label)
                ->addAttribute("required", "true")
                ->addAttribute("autocomplete", "false");
        }
        $this->addField(
            SelectWidget::create("country")->setOptions(
                \CoreDB::database()->select("countries", "c")
                ->select("c", ["ID", "name"])
                ->execute()->fetchAll(\PDO::FETCH_KEY_PAIR)
            )->setValue(
                DynamicModel::get([
                    "code" => Variable::getByKey("default_country_code")->value->getValue()
                ], "countries")->ID->getValue()
            )
            ->removeClass("selectpicker")
            ->setLabel(Translation::getTranslation("country"))
            ->addAttribute("required", "true")
        );
    }

    public function getFormId(): string
    {
        return "register_form";
    }

    public function getTemplateFile(): string
    {
        return "register-form.twig";
    }

    public function validate(): bool
    {
        foreach ($this->fields as $fieldName => $field) {
            if (!$this->request[$fieldName]) {
                $this->setError($fieldName, Translation::getTranslation("cannot_empty", [
                    $this->fields[$fieldName]->label
                ]));
            }
        }
        if ($this->request["password"] != $this->request["password_again"]) {
            $this->setError(
                "password",
                Translation::getTranslation("password_match_error")
            );
        } elseif (
            !User::validatePassword($this->request["password"])
        ) {
            $this->setError(
                "password",
                Translation::getTranslation("password_validation_error")
            );
        }
        if (!DynamicModel::get(["ID" => $this->request["country"]], "countries")) {
            $this->setError("country", Translation::getTranslation("cannot_empty", [
                $this->fields["country"]->label
            ]));
        }
        return empty($this->errors);
    }

    public function submit()
    {
        try {
            $user = new User();
            $mapData = $this->request;
            $mapData["active"] = 1;
            $mapData["username"] = $this->generateUsername($this->request["email"]);
            $mapData["pay_optional_at_checkout"] = 1;
            $mapData["email_verification_key"] = hash(
                "sha256",
                $user->getFullName() . $user->email->getValue() . microtime()
            );
            $mapData["address"] = [
                [
                    "user" => $user->ID->getValue(),
                    "address" => $this->request["address"],
                    "town" => $this->request["town"],
                    "county" => $this->request["county"],
                    "postalcode" => $this->request["postalcode"],
                    "country" => $this->request["country"],
                    "default" => 1,
                ]
            ];
            $user->map($mapData);
            $user->save();
            
            $verifyUrl = VerifyController::getUrl() . "{$user->ID}/" . $user->email_verification_key->getValue();
            \CoreDB::HTMLMail(
                $user->email->getValue(),
                Translation::getTranslation("email_verification"),
                Translation::getEmailTranslation("email_verification", [
                    $user->getFullName(), $verifyUrl, $verifyUrl
                ]),
                $user->getFullName()
            );
            
            $_SESSION[BASE_URL . "-UID"] = $user->ID;
            CoreDB::goTo(WelcomeController::getUrl());
        } catch (Exception $ex) {
            $this->setError("", $ex->getMessage());
        }
    }

    public function generateUsername(string $email)
    {
        $mailStart = explode("@", $email)[0];
        $tempUserName = $mailStart;
        while (User::getUserByUsername($tempUserName)) {
            $tempUserName = $mailStart . random_int(0, 100);
        }
        return $tempUserName;
    }
}
