<?php

namespace App\Form;

use App\Entity\Basket\Basket;
use App\Entity\PaymentMethod;
use App\Lib\IsBankClient;
use Exception;
use Omnipay\Common\Exception\InvalidCreditCardException;
use Src\Entity\Translation;
use Src\Form\Form;
use Src\Form\Widget\InputWidget;

class PaymentForm extends Form
{
    public $basket;
    public string $method = "POST";
    private ?IsBankClient $isBankClient = null;
    public array $paymentMethods;
    public function __construct(Basket $basket)
    {
        parent::__construct();
        $controller = \CoreDB::controller();
        $controller->addJsFiles("dist/payment/payment.js");
        $controller->addCssFiles("dist/payment/payment.css");
        $this->basket = $basket;
        $this->isBankClient = new IsBankClient($this->basket);
        $this->paymentMethods = PaymentMethod::getAll(["user" => \CoreDB::currentUser()->ID->getValue()]);

        $this->addField(
            InputWidget::create("pay_with_saved_credit_card")
            ->setValue(
                Translation::getTranslation("pay_with_saved_credit_card")
            )->setType("submit")
            ->removeClass("form-control")
            ->addClass("btn btn-outline-primary")
        );
        $this->addField(
            InputWidget::create("card_holder")
            ->setLabel(
                Translation::getTranslation("card_holder")
            )->addAttribute("size", "15")
            ->addAttribute("placeholder", Translation::getTranslation("card_holder"))
        );
        $this->addField(
            InputWidget::create("card_number")
            ->setLabel(
                Translation::getTranslation("card_number")
            )->setType("tel")
            ->addClass("w-100")
            ->addAttribute("placeholder", Translation::getTranslation("card_number"))
            ->addAttribute("inputmode", "numeric")
        );

        $this->addField(
            InputWidget::create("card_expire")
            ->setLabel(
                Translation::getTranslation("card_expire")
            )->addAttribute("size", "6")
            ->addAttribute("placeholder", "mm/yyyy")
            ->addAttribute("inputmode", "numeric")
        );

        $this->addField(
            InputWidget::create("card_cvv")
            ->setLabel(
                "CVV"
            )->addAttribute("size", "3")
            ->addAttribute("placeholder", "CVV")
            ->addAttribute("inputmode", "numeric")
        );

        $this->addField(
            InputWidget::create("pay")
            ->setValue(
                Translation::getTranslation("pay_now")
            )->setType("submit")
            ->removeClass("form-control")
            ->addClass("btn btn-outline-primary align-self-end")
        );

        $this->addField(
            InputWidget::create("save_my_card")
            ->setLabel(
                Translation::getTranslation("save_my_card")
            )->setType("checkbox")
            ->removeClass("form-control")
        );
    }

    public function getFormId(): string
    {
        return "payment-form";
    }

    public function getTemplateFile(): string
    {
        return "payment-form.twig";
    }

    public function validate(): bool
    {
        try {
            $response = $this->isBankClient->makePayment($this);
            if ($response->isRedirect()) {
                if ($this->request["save_my_card"]) {
                    $paymentMethod = new PaymentMethod();
                    $paymentMethod->map($this->request);
                    $paymentMethod->user->setValue(
                        \CoreDB::currentUser()->ID->getValue()
                    );
                    $paymentMethod->verified->setValue(1);
                    $paymentMethod->card_expire->setValue(
                        date_format(
                            date_create_from_format("d/m/Y", "01/" . $this->request["card_expire"]),
                            "d-m-Y"
                        )
                    );
                    $paymentMethod->save();
                }
                $response->redirect();
                die();
            } else {
                $this->setError("", $response->getMessage());
                //$this->restoreValues();
                return false;
            }
            return true;
        } catch (InvalidCreditCardException $ex) {
            $this->setError("card_number", Translation::getTranslation("enter_valid_card_number"));
        } catch (Exception $ex) {
            $this->setError("", $ex->getMessage());
        }
        return false;
    }

    public function submit()
    {
    }
}
