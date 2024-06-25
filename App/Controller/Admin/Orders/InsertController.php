<?php

namespace App\Controller\Admin\Orders;

use App\Controller\Admin\OrdersController;
use App\Entity\Basket\Basket;
use CoreDB\Kernel\Router;
use Src\Controller\AccessdeniedController;
use Src\Controller\NotFoundController;
use Src\Entity\Translation;
use Src\Form\InsertForm;

class InsertController extends OrdersController
{
    public ?Basket $order;
    public InsertForm $orderInsertForm;

    public function preprocessPage()
    {
        if (isset($this->arguments[0]) && $this->arguments[0]) {
            $this->order = Basket::get($this->arguments[0]);
            if (!$this->order) {
                Router::getInstance()->route(NotFoundController::getUrl());
            }
            $user = \CoreDB::currentUser();
            if (
                $user->isUserInRole("Order Manager") &&
                $user->authorized_branch->getValue()
            ) {
                $branches = array_map(function ($el) {
                    return $el["branch"];
                }, $user->authorized_branch->getValue());
                if (!in_array($this->order->branch->getValue(), $branches)) {
                    Router::getInstance()->route(AccessdeniedController::getUrl());
                }
            };
            $title = Translation::getTranslation("edit") . " | ";
            $orderAddress = $this->order->order_address->getValue();
            if ($orderAddress) {
                $title .= $orderAddress[0]["company_name"] . " - " . $orderAddress[0]["account_number"];
            } else {
                $title .= $this->order->ID;
            }
        } else {
            $this->order = new Basket();
            $title = Translation::getTranslation("create_order");
            $this->addJsCode(
                "$(function(){
                    $(\"*[id='input_orders[type]']\").on('change', function(){
                        if(this.value == 'collection'){
                            $('#branch-selection').show();
                        }else{
                            $('#branch-selection').hide();
                        }
                    });
                })"
            );
        }
        $this->setTitle($title);
        $this->orderInsertForm = $this->order->getForm();
        $this->orderInsertForm->processForm();
        $this->orderInsertForm->addClass("p-3");
    }

    public function echoContent()
    {
        return $this->orderInsertForm;
    }
}
