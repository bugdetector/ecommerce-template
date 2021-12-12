<?php

namespace App\Form;

use Src\Entity\Translation;
use Src\Entity\Variable;
use Src\Form\Form;
use Src\Form\Widget\CaptchaWidget;
use Src\Form\Widget\FormWidget;
use Src\Form\Widget\InputWidget;
use Src\Form\Widget\TextareaWidget;
use Src\Lib\CaptchaService;
use Src\Views\TextElement;

class ContactForm extends Form
{

    public string $method = "POST";

    public function getFormId(): string
    {
        return "contact-form";
    }

    public function __construct()
    {
        parent::__construct();
        $this->addAttribute("id", $this->getFormId());

        $this->addField(
            TextElement::create(
                Translation::getTranslation("contactus_info")
            )->setTagName("div")
            ->addClass("mb-4 alert alert-primary")
        );

        $this->addField(
            InputWidget::create("name")
            ->setLabel(Translation::getTranslation("name"))
            ->addClass("mb-2")
            ->addAttribute("required", "true")
            ->addAttribute("autocomplete", "false")
        );
        $this->addField(
            InputWidget::create("email")
            ->setLabel(Translation::getTranslation("email"))
            ->setType("email")
            ->addClass("mb-2")
            ->addAttribute("required", "true")
            ->addAttribute("autocomplete", "false")
        );

        $this->addField(
            TextareaWidget::create("message")
            ->setLabel(Translation::getTranslation("message"))
            ->addClass("mb-2")
            ->addAttribute("required", "true")
        );

        $this->addField(
            CaptchaWidget::create("captcha")
            ->setLabel(Translation::getTranslation("captcha"))
            ->addAttribute("required", "true")
        );

        $this->addField(
            InputWidget::create("send")
            ->setType("submit")
            ->setValue(Translation::getTranslation("send_mail"))
            ->removeClass("form-control")
            ->addClass("btn btn-primary mb-2")
        );
    }

    public function validate(): bool
    {
        foreach ($this->request as &$value) {
            $value = htmlspecialchars($value);
        }
        /** @var FormWidget $field */
        foreach ($this->fields as $fieldName => $field) {
            if ($field instanceof FormWidget && !@$this->request[$fieldName]) {
                $this->setError(
                    $fieldName,
                    Translation::getTranslation("_is_required", [
                        $field->label
                    ])
                );
            }
        }
        if (!filter_var($this->request["email"], FILTER_VALIDATE_EMAIL)) {
            $this->setError("email", Translation::getTranslation("enter_valid_mail"));
        }
        if (!CaptchaService::validateCaptcha($this->request["captcha"])) {
            $this->setError("captcha", Translation::getTranslation("wrong_captcha"));
        }
        return empty($this->errors);
    }

    public function submit()
    {
        $this->request = array_filter($this->request, "htmlspecialchars");
        $name = $this->request["name"];
        $email = $this->request["email"];
        $message = $this->request["message"];

        $message = Translation::getEmailTranslation("contact_us", [
            $name, $email, $message
        ], "en");

        \CoreDB::HTMLMail(
            Variable::getByKey("general_enquiries_email")->value->getValue(),
            "Contact Us Form - " . $name,
            $message,
            Variable::getByKey("site_name")->value->getValue()
        );
        $this->setMessage(
            Translation::getTranslation("message_sent_success")
        );
    }
}
