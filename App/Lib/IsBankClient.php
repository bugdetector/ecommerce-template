<?php

namespace App\Lib;

use App\Controller\Payment\PaymentsuccessController;
use App\Controller\PaymentController;
use App\Entity\Basket\Basket;
use App\Entity\Basket\BillingAddress;
use App\Entity\Basket\OrderAddress;
use App\Entity\Log\PaymentLog;
use App\Entity\PaymentMethod;
use CoreDB\Kernel\Messenger;
use Omnipay\Common\Message\ResponseInterface;
use Omnipay\NestPay\Gateway;
use Omnipay\Nestpay\Message\CompletePaymentResponse;
use Omnipay\Nestpay\Message\PaymentResponse;
use Omnipay\Omnipay;
use Src\Entity\DynamicModel;
use Src\Entity\Translation;
use Src\Form\Form;

class IsBankClient
{

    private const OPERATION_TYPE = "Auth";

    public ?Basket $basket;
    public Gateway $gateway;
    public ResponseInterface $response;

    public function __construct(?Basket $basket)
    {
        $this->basket = $basket;
        $this->gateway = Omnipay::create("Nestpay");
        $this->gateway->setUserName(OMNIPAY_USERNAME);
        $this->gateway->setStoreKey(OMNIPAY_STOREKEY);
        $this->gateway->setFirmName(OMNIPAY_FIRMNAME);
        $this->gateway->setClientId(OMNIPAY_CLIENT_ID);
        $this->gateway->setParameter("storetype", "3d_pay_hosting");
        $this->gateway->setPassword(OMNIPAY_PASSWORD);
        $this->gateway->setCurrency(OMNIPAY_CURRENCY);
        if (ENVIROMENT != "production") {
            $this->gateway->setBank("test");
            $this->gateway->setTestMode(true);
            if (get_class(\CoreDB::controller()) == PaymentController::class) {
                \CoreDB::messenger()->createMessage(
                    Translation::getTranslation("payment_test_mode"),
                    Messenger::INFO
                );
            }
        } else {
            $this->gateway->setBank("isbank");
        }
    }

    public function makePayment(Form $form): ?PaymentResponse
    {
        if (@$form->request["pay_with_saved_credit_card"]) {
            /** @var PaymentMethod */
            $paymentMethod = PaymentMethod::get([
                "user" => \CoreDB::currentUser()->ID->getValue(),
                "ID" => @$form->request["saved_card"]
            ]);
            if (!$paymentMethod) {
                throw new \Exception(
                    Translation::getTranslation("please_select_a_card")
                );
            }
            $expiryTime = strtotime($paymentMethod->card_expire->getValue());
            $expiryMonth = date("m", $expiryTime);
            $expiryYear = date("Y", $expiryTime);
            $cvv = $paymentMethod->card_cvv->getValue();
            $cardHolder = $paymentMethod->card_holder->getValue();
            $cardNumber = $paymentMethod->card_number->getValue();
        } else {
            $expiryTime = strtotime(
                date_format(
                    date_create_from_format("d/m/Y", "01/" . $form->request["card_expire"]),
                    "d-m-Y"
                )
            );
            $expiryMonth = date("m", $expiryTime);
            $expiryYear = date("Y", $expiryTime);
            $cvv = $form->request["card_cvv"];
            $cardHolder = $form->request["card_holder"];
            $cardNumber = str_replace(" ", "", $form->request["card_number"]);
        }
        $currentUser = \CoreDB::currentUser();
        /** @var OrderAddress */
        $orderAdress = OrderAddress::get(["order" => $this->basket->ID->getValue()]);
        /** @var BillingAddress */
        $billingAddress = BillingAddress::get(["order" => $this->basket->ID->getValue()]);
        $options = [
            "number" => $cardNumber,
            "expiryMonth" => $expiryMonth,
            "expiryYear" => $expiryYear,
            "cvv" => $cvv,
            "firstname" => $cardHolder
        ];
        $this->gateway->setDeliveryName($orderAdress->company_name->getValue());
        $this->gateway->setBillName($orderAdress->company_name->getValue());
        $paymentLogCount = count(PaymentLog::getAll(["order" => $this->basket->ID->getValue()]));
        /** @var ResponseInterface */
        $this->response = $this->gateway->purchase(
            [
            'installment'  => '1', # Taksit
            //'moneypoints'  => 1.00, // Set money points (Maxi puan gir)
            'amount'        => $this->basket->total->getValue() -
                               $this->basket->paid_amount->getValue(),
            'type'          => 'Auth',
            'orderid'       => $this->basket->ID->getValue() . ($paymentLogCount ? "-{$paymentLogCount}" : ""),
            'card'          => $options,
            "islemtipi" => self::OPERATION_TYPE,
            "lang" => Translation::getLanguage(),
            'returnUrl'     => PaymentsuccessController::getUrl() . "?basket={$this->basket->ID}",
            'cancelUrl'     => PaymentsuccessController::getUrl() . "?basket={$this->basket->ID}",
            "storeType" => "3d_pay_hosting",
            "Email" => $currentUser->email->getValue(),
            "Fadres" => $billingAddress->address->getValue(),
            "Fadres2" => $billingAddress->town->getValue(),
            "Filce" => $billingAddress->town->getValue(),
            "Fil" => $billingAddress->county->getValue(),
            "Fpostakodu" => $billingAddress->postalcode->getValue(),
            "Fulkekodu" => $billingAddress->county->getValue(),
            "Fulke" => $billingAddress->country->getValue() ?
            DynamicModel::get($billingAddress->country->getValue(), "countries")->code->getValue() : "GB",
            "NakliyeFirma" => $orderAdress->company_name->getValue(),
            "tismi" => $currentUser->getFullName(),
            "tadres" => $orderAdress->address->getValue(),
            "tadres2" => $orderAdress->town->getValue(),
            "tilce" => $orderAdress->town->getValue(),
            "til" => $orderAdress->county->getValue(),
            "tpostakodu" => $orderAdress->postalcode->getValue(),
            "tulkekod" => $orderAdress->country->getValue() ?
            DynamicModel::get($orderAdress->country->getValue(), "countries")->code->getValue() : "GB",
            "tel" => $currentUser->phone->getValue(),
            ]
        )->send();

        return $this->response;
    }

    public function completePurchase(): ?CompletePaymentResponse
    {
        return $this->gateway->completePurchase()->send();
    }
}
