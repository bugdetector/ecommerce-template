<?php

namespace App\Form;

use App\Entity\Basket\BillingAddress;
use App\Entity\Basket\OrderAddress;
use App\Entity\UserAddress;
use CoreDB;
use Src\Entity\Translation;
use Src\Form\Form;
use Src\Form\Widget\InputWidget;

class ChangeAccountForm extends Form
{

    public string $method = "POST";
    public function __construct()
    {
        parent::__construct();
        $this->addField(
            InputWidget::create("old_account_number")
            ->setLabel(Translation::getTranslation('old_account_number'))
            ->addAttribute('required', 'true')
        );
        $this->addField(
            InputWidget::create("new_account_number")
            ->setLabel(Translation::getTranslation('new_account_number'))
            ->addAttribute('required', 'true')
        );
        $this->addField(
            InputWidget::create("change")
            ->setType("submit")
            ->setValue(
                Translation::getTranslation("change")
            )->removeClass("form-control")
            ->addClass("btn btn-primary mt-2 float-right")
        );
    }

    public function getFormId(): string
    {
        return 'change_account_form';
    }
    public function validate(): bool
    {
        $account = UserAddress::get([
            'account_number' => $this->request['old_account_number']
        ], false);
        if (!$account) {
            $this->setError(
                'old_account_number',
                Translation::getTranslation('account_number_not_found')
            );
        }
        return empty($this->errors);
    }
    public function submit()
    {
        $oldAccountNumber =  $this->request['old_account_number'];
        $newAccountNumber =  $this->request['new_account_number'];
        $fields = [
            'account_number' => $newAccountNumber
        ];
        $res = CoreDB::database()->update(UserAddress::getTableName(), $fields)
        ->condition(UserAddress::getTableName() . '.account_number', $oldAccountNumber)
        ->execute();
        CoreDB::database()->update(OrderAddress::getTableName(), $fields)
        ->condition(OrderAddress::getTableName() . '.account_number', $oldAccountNumber)
        ->execute();
        CoreDB::database()->update(BillingAddress::getTableName(), $fields)
        ->condition(BillingAddress::getTableName() . '.account_number', $oldAccountNumber)
        ->execute();
        $this->setMessage(
            Translation::getTranslation('change_success')
        );
    }
}
