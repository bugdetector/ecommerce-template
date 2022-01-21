<?php

namespace App\Form;

use App\Controller\Checkout\ConfirmController;
use App\Controller\ProductsController;
use App\Entity\Basket\Basket;
use App\Entity\Basket\VoucherCode;
use App\Exception\BasketException;
use App\Views\BasketInfo;
use CoreDB;
use CoreDB\Kernel\Database\QueryCondition;
use CoreDB\Kernel\Messenger;
use Src\Entity\Translation;
use Src\Entity\Variable;
use Src\Form\Form;
use Src\Form\Widget\InputWidget;
use Src\Form\Widget\TextareaWidget;
use Src\Views\Link;
use Src\Views\TextElement;
use Src\Views\ViewGroup;

class CheckoutForm extends Form
{
    public Basket $basket;
    public ?VoucherCode $code = null;
    public string $method = "POST";

    public function getFormId(): string
    {
        return "checkout_form";
    }

    public function processForm()
    {
        $this->basket = Basket::getUserBasket();
        $this->addField(new BasketInfo($this->basket, true));
        $confirmButton = ViewGroup::create("button", "btn btn-primary form-control h-100")
        ->addAttribute("type", "submit")
        ->addAttribute("value", "checkout")
        ->addField(
            TextElement::create(
                Translation::getTranslation("checkout") . " <i class='fa fa-check'></i>"
            )->setIsRaw(true)
        );
        if (
            Variable::getByKey("non_login_order")->value->getValue() &&
            !CoreDB::currentUser()->isLoggedIn()
        ) {
            if (Variable::getByKey("collection_order_enabled")->value->getValue()) {
                $this->addField(
                    $this->basket->type->getWidget()
                    ->setName("type")
                );
            }
            $this->addField(
                $this->basket->order_address->getWidget()
            );
        }
        $this->addField(
            InputWidget::create("ref")
            ->setLabel(
                Translation::getTranslation("your_ref_optional")
            )->setValue($this->basket->ref->getValue())
        )->addField(
            TextareaWidget::create("order_notes")
            ->setLabel(
                Translation::getTranslation("order_notes")
            )->setValue($this->basket->order_notes->getValue())
        )->addField(
            InputWidget::create("voucher_code")
            ->setLabel(
                Translation::getTranslation("voucher_code")
            )->setValue(@$this->request["voucher_code"] ?: "")
        )->addField(
            ViewGroup::create("div", "row p-3")
            ->addField(
                ViewGroup::create("div", "col-6")
                ->addField(
                    Link::create(
                        ProductsController::getUrl(),
                        TextElement::create(
                            "<i class='fa fa-arrow-left'></i> " . Translation::getTranslation("continue_shopping")
                        )->setIsRaw(true)
                    )->addClass("btn btn-outline-info form-control h-100")
                )
            )->addField(
                ViewGroup::create("div", "col-6")
                ->addField(
                    $confirmButton
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
        $usePostcodeList = Variable::getByKey("use_postcode_list")->value->getValue();
        if ($usePostcodeList && !$this->basket->isDeliveryDayIsValid($this->request["delivery_date"])) {
            $this->setError(
                "delivery_date",
                Translation::getTranslation("please_select_valid_day")
            );
        }
        if ($this->request["voucher_code"]) {
            $query = \CoreDB::database()->select(VoucherCode::getTableName(), "vc");
            $dateCondition = new QueryCondition($query);
            $dateCondition->condition("vc.always_available", 0)
            ->condition("vc.start_date", date("Y-m-d"), "<=")
            ->condition("vc.end_date", date("Y-m-d"), ">=");
            $query->condition($dateCondition)
            ->condition("vc.always_available", 1, "OR")
            ->condition("vc.code", $this->request["voucher_code"])
            ->select("vc", ["ID"]);
            $codeId = $query->execute()->fetchColumn();
            if ($codeId) {
                $this->code = VoucherCode::get($codeId);
            } else {
                $this->setError(
                    "voucher_code",
                    Translation::getTranslation("invalid_voucher_code")
                );
            }
        }
        return empty($this->errors);
    }

    public function submit()
    {
        $basketEdit = [
            "ref" => $this->request["ref"],
            "order_notes" => $this->request["order_notes"],
            "delivery_date" => @$this->request["delivery_date"]
        ];
        if (
            Variable::getByKey("non_login_order")->value->getValue() &&
            !CoreDB::currentUser()->isLoggedIn()
        ) {
            if (Variable::getByKey("collection_order_enabled")->value->getValue()) {
                $basketEdit["type"] = $this->request["type"];
            }
            $basketEdit["order_address"] = $this->request["orders"]["order_address"];
            $basketEdit["billing_address"] = $this->request["orders"]["order_address"];
        }
        if ($this->code) {
            \CoreDB::messenger()->createMessage(
                Translation::getTranslation("voucher_code_applied", [
                    $this->code->code->getValue()
                ]),
                Messenger::SUCCESS
            );
            $basketEdit["applied_voucher_code"] = $this->code->ID->getValue();
        } else {
            $basketEdit["applied_voucher_code"] = null;
        }
        $this->basket->map($basketEdit);
        try {
            \CoreDB::database()->beginTransaction();
            $this->basket->checkout();
            $this->basket->save();
            \CoreDB::database()->commit();
            \CoreDB::goTo(ConfirmController::getUrl(), ["basket" => $this->basket->ID->getValue()]);
        } catch (BasketException $ex) {
            \CoreDB::database()->rollback();
            \CoreDB::controller()->createMessage(
                $ex->getMessage()
            );
        }
    }
}
