<?php

namespace App\Controller\Admin;

use App\AdminTheme\EcommerceAdminController;
use App\Controller\NotFoundController;
use App\Entity\Basket\VoucherCode;
use CoreDB\Kernel\Router;
use Src\Entity\Translation;
use Src\Form\InsertForm;
use Src\Form\SearchForm;

class VoucherController extends EcommerceAdminController
{
    public ?SearchForm $voucherForm = null;
    public ?InsertForm $insertForm = null;
    public VoucherCode $code;
    public function preprocessPage()
    {
        if (@$this->arguments[0]) {
            switch ($this->arguments[0]) {
                case "add":
                    $this->code = new VoucherCode();
                    break;
                default:
                    $this->code = VoucherCode::get($this->arguments[0]);
            }
            if ($this->code) {
                $this->insertForm = $this->code->getForm();
                $this->insertForm->processForm();
                $this->setTitle(
                    Translation::getTranslation("voucher_code") . " | " .
                    (
                        $this->code->ID->getValue() ? $this->code->title :
                        Translation::getTranslation("add")
                    )
                );
            } else {
                Router::getInstance()->route(NotFoundController::getUrl());
            }
        } else {
            $this->setTitle(Translation::getTranslation("voucher_codes"));
            $this->code = new VoucherCode();
            $this->voucherForm = SearchForm::createByObject($this->code);
        }
        $this->actions = $this->code->actions();
    }

    public function echoContent()
    {
        return $this->insertForm ?: $this->voucherForm;
    }
}
