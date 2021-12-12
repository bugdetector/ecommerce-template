<?php

namespace App\Form;

use App\Controller\Admin\Users\InsertController;
use CoreDB;
use Src\Entity\Role;
use Src\Entity\User;
use Src\Form\UserInsertForm as FormUserInsertForm;
use Src\Form\Widget\CollapsableWidgetGroup;
use Src\Views\ViewGroup;

class UserInsertForm extends FormUserInsertForm
{
    public function __construct(User $user)
    {
        parent::__construct($user);
        unset(
            $this->fields[$user->entityName . "[email_verification_key]"],
            $this->fields[$user->entityName . "[product_card_list_option]"],
            $this->fields[$user->entityName . "[favorite_card_list_option]"],
            $this->fields[$user->entityName . "[bespoke_card_list_option]"]
        );
        $orderManagerRole = Role::get(["role" => "Order Manager"]);
        \CoreDB::controller()->addJsCode("$(function(){
            $(\"*[id='input_users[roles][]']\").on('change', function(){
                let collapseGroup = $(\"[id='input_users[authorized_branch][]']\");
                let addBtn = $(\".add-new-entity[data-entity='authorized_branch']\");
                if($(this).val().includes('{$orderManagerRole->ID}')){
                    collapseGroup.show();
                    addBtn.show();
                }else{
                    collapseGroup.hide();
                    addBtn.hide();
                }
            }).trigger('change');
        })");
    }

    public function validate(): bool
    {
        if (!isset($this->request[$this->object->entityName]["roles"])) {
            $this->request[$this->object->entityName]["roles"] = [];
        }
        if (!isset($this->request[$this->object->entityName]["additional_delivery_address"])) {
            $this->request[$this->object->entityName]["additional_delivery_address"] = [];
        }
        return parent::validate();
    }

    protected function restoreValues()
    {
        parent::restoreValues();
        /** @var CollapsableWidgetGroup */
        $addressFields = @$this->fields["custom_users[address][]"];
        if ($addressFields) {
            foreach ($this->request["users"]["address"] as $index => $addressData) {
                $index = 0;
                foreach ($addressData as $fieldName => $value) {
                    /** @var ViewGroup */
                    $fields = $addressFields->fieldGroup->fields[0]->content;
                    $fields->fields[$index]->setValue($value);
                    $index++;
                }
            }
        }
    }

    protected function submitSuccess()
    {
        CoreDB::goTo(InsertController::getUrl() . $this->object->ID);
    }

    protected function deleteSuccess(): string
    {
        CoreDB::goTo(
            InsertController::getUrl()
        );
    }
}
