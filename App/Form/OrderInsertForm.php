<?php

namespace App\Form;

use App\Entity\Basket\Basket;
use App\Entity\Branch;
use App\Entity\Log\PaymentLog;
use App\Entity\Product\Product;
use App\Entity\UserAddress;
use App\Queries\AccountFinderQuery;
use App\Queries\UserFinderQuery;
use Src\Entity\Translation;
use Src\Entity\Variable;
use Src\Form\InsertForm;
use Src\Form\Widget\FinderWidget;
use Src\Views\CollapsableCard;
use Src\Views\Table;

class OrderInsertForm extends InsertForm
{
    public Basket $order;
    public ?Branch $branch;
    /**
     * @var PaymentLog[]
     */
    public array $paymentLogs;

    public ?CollapsableCard $paymentLogView = null;

    public function __construct(Basket $order)
    {
        parent::__construct($order);
        $this->order = $order;
        $this->branch = Branch::get($this->order->branch->getValue());
        $this->paymentLogs = PaymentLog::getAll([
            "order" => $order->ID->getValue(),
            "is_success" => 1
        ]);
        $this->addClass("row");
        $this->fields["orders[order_address][]"]->fieldGroup->fields[0]->setOpened(false);
        $this->fields["orders[billing_address][]"]->fieldGroup->fields[0]->setOpened(false);

        if (!empty($this->paymentLogs)) {
            $this->paymentLogView = CollapsableCard::create(
                Translation::getTranslation("payment")
            );
            $this->paymentLogView->setId("payment-log");
            $paymentLogTable = new Table();
            $paymentLogTable->setHeaders([
                Translation::getTranslation("paid_amount"),
                Translation::getTranslation("Transaction Ref"),
            ]);
            $tableData = [];
            $currencyIcon = Variable::getByKey("currency_icon")->value->getValue();
            foreach ($this->paymentLogs as $paymentLog) {
                $tableData[] = [
                    "{$currencyIcon}" . number_format($paymentLog->amount->getValue(), 2, ".", ""),
                    $paymentLog->transaction_ref
                ];
            }
            
            $paymentLogTable->setData($tableData);

            $this->paymentLogView->setContent($paymentLogTable);
        }
    }

    public function getTemplateFile(): string
    {
        return "order-insert-form.twig";
    }

    public function getProductById($id)
    {
        return Product::get($id);
    }

    public function getAccountWidget()
    {
        return FinderWidget::create("account_number")
        ->setFinderClass(AccountFinderQuery::class)
        ->setValue(\CoreDB::currentUser()->shipping_address->getValue())
        ->setLabel(
            Translation::getTranslation("select_for_account")
        );
    }

    public function submit()
    {
        if (!$this->order->ID->getValue()) {
            $accountNumber = $this->request["account_number"];
            /** @var UserAddress */
            $address = UserAddress::get($accountNumber, false);
            $this->order->map([
                "user" => $address->user->getValue(),
                "order_address" => [$address->toArray()],
                "billing_address" => [$address->toArray()]
            ]);
            $this->order->save();
            $this->order->map([
                "is_ordered" => 1
            ]);
        }
        parent::submit();
        $this->order->checkout();
    }
}
