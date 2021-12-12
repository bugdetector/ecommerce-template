<?php

namespace App\Queries;

use App\Controller\Admin\AjaxController as AdminAjaxController;
use App\Controller\AjaxController;
use App\Controller\Admin\Orders\InsertController;
use App\Controller\Admin\OrdersController;
use App\Controller\Admin\Users\InsertController as UsersInsertController;
use App\Entity\Branch;
use App\Entity\CustomUser;
use CoreDB\Kernel\Database\SelectQueryPreparerAbstract;
use Src\Controller\Admin\AjaxController as ControllerAdminAjaxController;
use Src\Entity\Translation;
use Src\Entity\Variable;
use Src\Entity\ViewableQueries;
use Src\Views\Link;
use Src\Views\TextElement;
use Src\Views\ViewGroup;

class OrdersQuery extends ViewableQueries
{

    public function __construct(string $tableName = null, array $mapData = [])
    {
        parent::__construct($tableName, $mapData);
        \CoreDB::controller()->addJsCode(
            "$(function(){
                $('.send-payment-request').on('click', function(e){
                    e.preventDefault();
                    let link = $(this);
                    bootbox.confirm('" .
                        Translation::getTranslation("are_you_sure_want_to_send_payment_request") .
                    "', function(response){ 
                        if(response){ 
                            location.assign(link.attr('href'));
                        } 
                    });
                })
            })"
        );
    }

    public static function getInstance()
    {
        return parent::getByKey("order_list");
    }

    public function getSearchFormFields(bool $translateLabel = true): array
    {
        $searchFormFields = parent::getSearchFormFields($translateLabel);
        $searchFormFields["paid_online"]->setOptions([
            1 => Translation::getTranslation("yes"),
            0 => Translation::getTranslation("no")
        ]);
        $cartIdSearch = $searchFormFields["ID"]
        ->setLabel("Cart Id")
        ->setName("basket.ID");
        unset($searchFormFields["ID"]);
        $searchFormFields["basket.ID"] = $cartIdSearch;
        $user = \CoreDB::currentUser();
        if (
            $user->isUserInRole("Order Manager") &&
            $user->authorized_branch->getValue()
        ) {
            unset($searchFormFields['branch']);
        }
        return $searchFormFields;
    }

    public function getResultHeaders(bool $translateLabel = true): array
    {
        $controller = \CoreDB::controller();
        $controller->addJsFiles("dist/user_comment/user_comment.js");
        $controller->addFrontendTranslation("comment");
        $controller->addFrontendTranslation("last_modified_message");
        $headers = parent::getResultHeaders($translateLabel);
        $headers["ID"] = "Cart Id";
        array_unshift(
            $headers,
            "",
        );
        unset(
            $headers["user"],
            $headers["comment_last_modified_date"],
            $headers["comment_last_modified_by"]
        );
        return $headers;
    }

    public function getResultQuery(): SelectQueryPreparerAbstract
    {
        $query = parent::getResultQuery();
        $query->select("users", ["ID AS user_id"]);
        $user = \CoreDB::currentUser();
        if (
            $user->isUserInRole("Order Manager") &&
            $user->authorized_branch->getValue()
        ) {
            $branches = array_map(function ($el) {
                return $el["branch"];
            }, $user->authorized_branch->getValue());
            $query->condition(
                "basket.branch",
                $branches,
                "IN"
            );
        }
        return $query;
    }

    public function postProcessRow(&$row): void
    {
        array_unshift(
            $row,
            "edit"
        );
        $row[0] = ViewGroup::create("div", "d-flex")
        ->addField(
            Link::create(
                InsertController::getUrl() . $row["ID"],
                ViewGroup::create("i", "fa fa-edit text-primary core-control")
            )
        )->addField(
            Link::create(
                AjaxController::getUrl() . "basketInvoice?basket-id={$row["ID"]}",
                ViewGroup::create("i", "fa fa-file-pdf text-danger core-control ml-3")
            )->addAttribute("target", "_blank")
        )->addField(
            Link::create(
                OrdersController::getUrl() . "?user=" . $row["user"],
                ViewGroup::create("i", "fa fa-gifts text-info core-control ml-3")
            )
        )->addField(
            Link::create(
                ControllerAdminAjaxController::getUrl() . "sendPaymentRequest?basket={$row["ID"]}",
                ViewGroup::create("i", "fa fa-envelope text-info core-control ml-3")
            )->addClass("send-payment-request")
        );
        $row["is_canceled"] = $row["is_canceled"] ?
            Translation::getTranslation("order_canceled") : "";
        $row["type"] = Translation::getTranslation($row["type"]);
        $currencyIcon = Variable::getByKey("currency_icon")->value->getValue();
        $paidInfo = $row["paid_amount"] ? Translation::getTranslation(
            "{$currencyIcon}%.2f Paid, {$currencyIcon}%.2f remaining",
            [$row["paid_amount"], $row["total"] - $row["paid_amount"]],
        ) : null;
        $row["total"] = Variable::getByKey("currency_icon")->value->getValue() .
            number_format($row["total"], 2, ".", ",");
        $row["delivery_date"] = date("d.m.Y", strtotime($row["delivery_date"]));
        $row["paid_amount"] = $paidInfo;
        $row["order_time"] = date("d.m.Y H:i:s", strtotime($row["order_time"]));
        $row["branch"] = $row["branch"] ? Branch::get($row["branch"])->name->getValue() : "";
        $row["account_number"] = Link::create(
            UsersInsertController::getUrl() . $row["user"],
            $row["account_number"]
        )->addAttribute("target", "_blank");
        $row['comment'] = Link::create(
            "#",
            ViewGroup::create("i", "fa fa-comment")
        )->addClass("btn btn-info user-comment-button")
        ->addAttribute("data-user", $row["user_id"] ?: "")
        ->addAttribute("data-comment", $row['comment'] ?: "");
        /** @var CustomUser */
        $lastModifiedBy = $row["comment_last_modified_by"] ?
        CustomUser::get($row["comment_last_modified_by"]) : null;
        if ($lastModifiedBy) {
            $row['comment']
            ->addAttribute("data-modified-by", $lastModifiedBy->getFullName())
            ->addAttribute(
                "data-modified-date",
                date("d-m-Y H:i:s", strtotime($row["comment_last_modified_date"]))
            );
        }
        unset(
            $row['user_id'],
            $row["comment_last_modified_date"],
            $row["comment_last_modified_by"],
        );
        unset($row["user"]);
        $row['paid_online'] = Translation::getTranslation(
            $row['paid_online'] ? 'yes' : 'no'
        );
        $row["cancel_time"] = $row["cancel_time"] ? date("d.m.Y H:i:s", strtotime($row["cancel_time"])) : null;
    }
}
