<?php

namespace App\Form;

use App\Controller\Checkout\SentController;
use App\Controller\CheckoutController;
use App\Controller\PaymentController;
use App\Entity\Basket\Basket;
use App\Views\BasketInfo;
use CoreDB;
use Exception;
use Src\Entity\DynamicModel;
use Src\Entity\Translation;
use Src\Form\Form;
use Src\Views\Link;
use Src\Views\TextElement;
use Src\Views\ViewGroup;

class SendOrderForm extends Form
{

    public string $method = "POST";
    public Basket $basket;
    public function __construct(Basket &$basket)
    {
        parent::__construct();
        $this->basket = $basket;
    }

    public function getFormId(): string
    {
        return "send_order_form";
    }

    public function processForm()
    {
        $addressData = $this->basket->type->getValue() == Basket::TYPE_DELIVERY ?
        $this->basket->order_address->getValue()[0] : null;
        $billingAdressData = $this->basket->billing_address->getValue()[0];
        unset(
            $addressData["order"],
            $addressData["default"],
            $billingAdressData["order"],
            $billingAdressData["default"]
        );
        if ($this->basket->type->getValue() == Basket::TYPE_DELIVERY) {
            $addressData["country"] = DynamicModel::get($addressData["country"], "countries")->name->getValue();
        }
        $billingAdressData["country"] = DynamicModel::get($billingAdressData["country"], "countries")->name->getValue();
        $sendOrderLabel =
            $this->basket->type->getValue() == Basket::TYPE_COLLECTION ||
            \CoreDB::currentUser()->pay_optional_at_checkout->getValue()
                ? "send_order" : "pay_and_send_order";
        $sendOrderButton = ViewGroup::create("button", "btn btn-primary mt-sm-0 form-control h-100")
        ->addAttribute("type", "submit")
        ->addAttribute("value", "send_order")
        ->addField(
            TextElement::create(
                Translation::getTranslation($sendOrderLabel) . " <i class='fa fa-check'></i>"
            )->setIsRaw(true)
        );
        $this->addField(new BasketInfo($this->basket));
        $this->addField(
            ViewGroup::create("div", "")->addField(
                TextElement::create(Translation::getTranslation("your_ref_optional"))
                    ->setTagName("p")
                    ->addClass("fw-bolder text-primary")
            )->addField(
                TextElement::create($this->basket->ref)
                    ->setTagName("p")
            )
        )->addField(
            ViewGroup::create("div", "")->addField(
                TextElement::create(Translation::getTranslation("order_notes"))
                    ->setTagName("p")
                    ->addClass("text-primary fw-bolder")
            )->addField(
                TextElement::create($this->basket->order_notes)
                    ->setTagName("p")
            )
        )
            ->addField(
                ViewGroup::create("div", "row")
                    ->addField(
                        ViewGroup::create("div", "col-4")
                        ->addField(
                            Link::create(
                                CheckoutController::getUrl(),
                                TextElement::create(
                                    "<i class='fa fa-arrow-left'></i> " . Translation::getTranslation("back")
                                )->setIsRaw(true)
                            )->addClass("btn btn-outline-info mb-2 form-control")
                        )
                    )
                    ->addField(
                        ViewGroup::create("div", "col-8 mb-4")
                            ->addField(
                                $sendOrderButton
                            )
                    )
            );

        parent::processForm();
    }

    public function validate(): bool
    {
        $notSatisfiedProducts = $this->basket->checkProductsNotSatisfys();
        if (!empty($notSatisfiedProducts)) {
            foreach ($notSatisfiedProducts as $product) {
                $this->setError(
                    "",
                    Translation::getTranslation(
                        "minimum_private_error",
                        [
                            $product->minimum_order_count->getValue() ?: Basket::getMinimumPrivateItemCount(),
                            $product->title->getValue() . " - " . $product->stockcode->getValue(),
                        ]
                    )
                );
            }
        }
        return empty($this->errors);
    }

    public function submit()
    {
        try {
            if (
                $this->basket->type->getValue() == Basket::TYPE_COLLECTION ||
                \CoreDB::currentUser()->pay_optional_at_checkout->getValue()
            ) {
                $this->basket->map([
                    "is_ordered" => 1,
                ]);
                $this->basket->save();
            } else {
                CoreDB::goTo(PaymentController::getUrl(), ["basket" => $this->basket->ID->getValue()]);
            }
        } catch (Exception $ex) {
            \CoreDB::messenger()->createMessage($ex->getMessage());
            return;
        }
        CoreDB::goTo(SentController::getUrl(), ["basket" => $this->basket->ID->getValue()]);
    }
}
