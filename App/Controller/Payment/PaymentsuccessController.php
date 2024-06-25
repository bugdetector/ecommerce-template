<?php

namespace App\Controller\Payment;

use App\Controller\PaymentController;
use App\Entity\Basket\Basket;
use App\Entity\Log\PaymentLog;
use App\Lib\IsBankClient;
use App\Theme\AppController;
use CoreDB\Kernel\Messenger;
use CoreDB\Kernel\Router;
use Src\Controller\AccessdeniedController;
use Src\Entity\Translation;
use Src\Entity\Variable;
use Src\Views\Image;
use Src\Views\Link;
use Src\Views\TextElement;
use Src\Views\ViewGroup;

class PaymentsuccessController extends AppController
{
    private IsBankClient $isBankClient;
    public ?Basket $basket = null;

    public function checkAccess(): bool
    {
        return true;
    }

    public function preprocessPage()
    {
        /** @var CompletePaymentResponse $response */
        $response = null;
        if (in_array(@$_POST["Response"], ["Success", "Approved"])) {
            try {
                $this->isBankClient = new IsBankClient(null);
                $response = $this->isBankClient->completePurchase();
                $this->basket = Basket::get($response->getOrderId());
            } catch (\Exception $ex) {
                Router::getInstance()->route(AccessdeniedController::getUrl());
            }
        } else {
            $this->basket = Basket::get(@$_GET["basket"]);
        }
        $paymentLog = null;
        if (!empty($_POST)) {
            $paymentLog = new PaymentLog();
            $paymentLog->map([
                "order" => $this->basket ? $this->basket->ID->getValue() : null,
                "response" => json_encode($_POST, JSON_PRETTY_PRINT),
                "is_success" => 0
            ]);
            $paymentLog->save();
        }
        if (
            $response && $response->isSuccessful()
        ) {
            $paid = $response->getAmount();
            /** @var PaymentLog */
            $logByTransactionId = PaymentLog::get(["transaction_ref" => $response->getTransactionId()]);
            if ($logByTransactionId) {
                $paymentLog->delete();
                $paymentLog = $logByTransactionId;
                $paymentLog->map([
                    "order" => $this->basket ? $this->basket->ID->getValue() : null,
                    "response" => json_encode($_POST, JSON_PRETTY_PRINT),
                ]);
            }
            $paymentLog->map([
                "is_success" => 1,
                "transaction_ref" => $response->getTransactionId(),
                "amount" => $paid
            ]);
            $paymentLog->save();
            $this->basket->map([
                "is_ordered" => 1,
                "paid_online" => 1,
                "transaction_id" => $response->getTransactionId(),
                "paid_amount" => $this->basket->total->getValue()
            ]);
            $this->basket->save();

            \CoreDB::HTMLMail(
                Variable::getByKey("payment_report_email")->value->getValue(),
                "Payment: " . htmlspecialchars($this->basket->order_id),
                Translation::getTranslation(
                    "Payment taken " . Variable::getByKey("currency_icon")->value->getValue()
                    . "%s from %s. Transaction number is %s.",
                    [
                        $paid,
                        $this->basket->order_id->getValue(),
                        $response->getTransactionId()
                    ]
                ),
                Variable::getByKey("site_name")->value->getValue()
            );
            $this->createMessage(
                Translation::getTranslation("payment_thanks_message", [$paid]),
                Messenger::SUCCESS
            );
            $this->setTitle(
                Translation::getTranslation("thank_you_for_order")
            );
        } else {
            $this->setTitle(
                Translation::getTranslation("payment_failed")
            );
            $this->createMessage(
                Translation::getTranslation("payment_failed")
            );
            if (@$_POST["ErrMsg"]) {
                $this->createMessage(
                    @$_POST["ErrCode"] . ": " . @$_POST["ErrMsg"]
                );
            }
        }
    }

    public function echoContent()
    {
        $buttonGroup = ViewGroup::create("div", "")
        ->addField(
            Link::create(
                BASE_URL,
                TextElement::create(
                    "🏠 " . Translation::getTranslation("Home")
                )->setIsRaw(true)
            )->addClass("btn btn-primary")
        );
        if ($this->basket->paid_amount->getValue() != $this->basket->total->getValue()) {
            $buttonGroup->addField(
                Link::create(
                    PaymentController::getUrl() . "?basket=" . ($this->basket ? $this->basket->ID : 0),
                    TextElement::create(
                        "⟲ " . Translation::getTranslation("try_again")
                    )->setIsRaw(true)
                )->addClass("btn btn-info")
            );
        }
        $content = ViewGroup::create("div", "d-flex flex-column align-items-center")
        ->addField(
            $buttonGroup
        );
        if ($this->basket->paid_amount->getValue()) {
            $content->addField(
                Image::create(
                    BASE_URL . "/assets/thank-you.png",
                    $this->title
                )->addClass("img-fluid h-100")
            );
        }
        return $content;
    }


    /** Hiding navbar */
    public function buildNavbar()
    {
    }
    public function buildCategoryNavbar()
    {
    }
    /**
     * Hiding cookie warning when returning from payment
     */
    protected function addDefaultTranslations()
    {
    }
}
