<?php

namespace App\Queries;

use App\Controller\AjaxController;
use App\Controller\Checkout\OrdeviewController;
use App\Controller\Checkout\SentController;
use App\Entity\Basket\Basket;
use CoreDB;
use CoreDB\Kernel\Database\SelectQueryPreparerAbstract;
use Src\Entity\Translation;
use Src\Entity\Variable;
use Src\Entity\ViewableQueries;
use Src\Views\Link;
use Src\Views\TextElement;

class MyOrdersQuery extends ViewableQueries
{
    public static function getInstance()
    {
        return parent::getByKey("my_orders_list");
    }

    public function getSearchFormFields(bool $translateLabel = true): array
    {
        $searchFormFields = parent::getSearchFormFields($translateLabel);
        $searchFormFields["ID"]->setLabel(
            Translation::getTranslation("Cart Id")
        );
        return $searchFormFields;
    }

    public function getResultHeaders(bool $translateLabel = true): array
    {
        $headers = parent::getResultHeaders($translateLabel);
        array_unshift(
            $headers,
            Translation::getTranslation("view"),
            Translation::getTranslation("pay_now"),
            Translation::getTranslation("download"),
            Translation::getTranslation("order_again")
        );
        unset($headers["paid_online"]);
        return $headers;
    }

    public function getResultQuery(): SelectQueryPreparerAbstract
    {
        $query = parent::getResultQuery();
        $query->condition("basket.user", CoreDB::currentUser()->ID->getValue());
        return $query;
    }

    public function postProcessRow(&$row): void
    {
        array_unshift(
            $row,
            "view",
            "pay",
            "download",
            "order_again"
        );
        $row[0] = Link::create(
            OrdeviewController::getUrl() . "?basket={$row["ID"]}",
            TextElement::create(
                "<i class='fa fa-eye'></i> " . Translation::getTranslation("view")
            )->setIsRaw(true)
        )->addClass("text-decoration-none btn btn-link mr-2 mb-2");
        if (strtotime($row["delivery_date"]) > strtotime("-2 month 23:59:59")) {
            if (
                $row["type"] == Basket::TYPE_DELIVERY &&
                !$row["is_canceled"] &&
                $row["total"] > $row["paid_amount"]
            ) {
                $row[1] = Link::create(
                    SentController::getUrl() . "?basket={$row["ID"]}",
                    TextElement::create(
                        "<i class='fa fa-credit-card'></i> " . Translation::getTranslation("pay_now")
                    )->setIsRaw(true)
                )->addClass("btn btn-success text-decoration-none mr-2 mb-2");
            } else {
                $row[1] = "";
            }
        } else {
            $row[1] = "";
        }
        if (strtotime($row["delivery_date"]) > strtotime("today 23:59:59")) {
            if (!$row["is_canceled"]) {
                $row[4] = Link::create(
                    "#",
                    TextElement::create(
                        "<i class='fa fa-times'></i> " . Translation::getTranslation("cancel_order")
                    )->setIsRaw(true)
                )->addClass("btn btn-outline-danger text-decoration-none mr-2 mb-2 cancel-order")
                ->addAttribute("data-order", $row["ID"]);
            } else {
                $row[4] = TextElement::create(
                    Translation::getTranslation("order_canceled")
                )->addClass("text-danger");
            }
        }
        $row[2] = Link::create(
            AjaxController::getUrl() . "basketInvoice?basket-id={$row["ID"]}",
            TextElement::create(
                "<i class='fa fa-file-pdf'></i> " . Translation::getTranslation("download")
            )->setIsRaw(true)
        )->addClass("text-decoration-none btn btn-link mr-2 mb-2");

        $row[3] = Link::create(
            AjaxController::getUrl() . "orderAgain?basket-id={$row["ID"]}",
            TextElement::create(
                "<i class='fa fa-undo-alt'></i> " . Translation::getTranslation("order_again")
            )->setIsRaw(true)
        )->addClass("text-decoration-none btn btn-link mr-2 mb-2");

        $row["total"] = Variable::getByKey("currency_icon")->value->getValue() .
            number_format($row["total"], 2, ".", ",");
        $row["order_time"] = date("d.m.Y", strtotime($row["order_time"]));
        unset($row["paid_online"]);
    }
}
