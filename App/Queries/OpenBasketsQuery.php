<?php

namespace App\Queries;

use App\Controller\Admin\AjaxController;
use App\Controller\Admin\Orders\InsertController;
use App\Controller\Admin\Users\InsertController as UsersInsertController;
use App\Entity\AppUser;
use CoreDB\Kernel\Database\SelectQueryPreparerAbstract;
use Src\Entity\Translation;
use Src\Entity\ViewableQueries;
use Src\Views\Link;
use Src\Views\TextElement;
use Src\Views\ViewGroup;

class OpenBasketsQuery extends ViewableQueries
{
    public function getSearchFormFields(bool $translateLabel = true): array
    {
        $searchFormFields = parent::getSearchFormFields($translateLabel);
        $cartIdSearch = $searchFormFields["ID"]
        ->setLabel("Cart Id")
        ->setName("basket.ID");
        $searchFormFields["basket.ID"] = $cartIdSearch;

        $lastUpdatedSearch = $searchFormFields["last_updated"]
        ->setName("basket.last_updated");

        unset($searchFormFields["ID"], $searchFormFields["last_updated"]);

        $searchFormFields["basket.ID"] = $cartIdSearch;
        $searchFormFields["basket.last_updated"] = $lastUpdatedSearch;
        return $searchFormFields;
    }

    public function getResultHeaders(bool $translateLabel = true): array
    {
        $controller = \CoreDB::controller();
        $controller->addJsFiles("ecommerce_theme/src/components/user_comment/user_comment.js");
        $controller->addFrontendTranslation("comment");
        $controller->addFrontendTranslation("last_modified_message");
        $controller->addFrontendTranslation("save");
        $controller->addJsCode(
            "$(function(){
                $(document).on('click', '.assign-button', function(e){
                    e.preventDefault();
                    $.ajax({
                        url: $(this).attr('href'),
                        success: function(){
                            location.reload();
                        }
                    })
                })
            })"
        );
        $headers = parent::getResultHeaders($translateLabel);
        $headers["ID"] = "Cart Id";
        array_unshift(
            $headers,
            ""
        );
        unset(
            $headers["comment_last_modified_date"],
            $headers["comment_last_modified_by"],
        );
        unset($headers['user']);
        return $headers;
    }

    public static function getInstance()
    {
        return parent::getByKey("open_baskets");
    }

    public function getResultQuery(): SelectQueryPreparerAbstract
    {
        $query = parent::getResultQuery();
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
            Link::create(
                InsertController::getUrl() . $row["ID"],
                TextElement::create(
                    "<i class='fa fa-eye core-control'></i>"
                )->setIsRaw(true)
            )
        );
        $row['account_number'] = Link::create(
            UsersInsertController::getUrl() . $row['user'],
            $row['account_number']
        )->addAttribute('target', '_blank');

        $row['comment'] = Link::create(
            "#",
            ViewGroup::create("i", "fa fa-comment")
        )->addClass("btn btn-sm user-comment-button")
        ->addClass($row["comment"] ? "btn-danger" : "btn-info")
        ->addAttribute("data-user", $row["user"] ?: "")
        ->addAttribute("data-comment", $row['comment'] ?: "");
        /** @var AppUser */
        $lastModifiedBy = $row["comment_last_modified_by"] ?
        AppUser::get($row["comment_last_modified_by"]) : null;
        if ($lastModifiedBy) {
            $row['comment']
            ->addAttribute("data-modified-by", $lastModifiedBy->getFullName())
            ->addAttribute(
                "data-modified-date",
                date("d-m-Y H:i:s", strtotime($row["comment_last_modified_date"]))
            );
        }

        /** @var AppUser */
        $dealer = $row['dealer'] ? AppUser::get($row['dealer']) : null;
        $row['dealer'] = $row['dealer'] ?
        ViewGroup::create("div", "d-flex flex-column")
        ->addField(TextElement::create($dealer->getFullName()))
        ->addField(
            Link::create(
                AjaxController::getUrl() . "assignBasketToMe?basket_id={$row["ID"]}&operation=unassign",
                Translation::getTranslation("unassign")
            )->addClass("assign-button")
        )
        :
        Link::create(
            AjaxController::getUrl() . "assignBasketToMe?basket_id={$row["ID"]}",
            Translation::getTranslation("assign_to_me")
        )->addClass("assign-button");
        unset(
            $row['user'],
            $row["comment_last_modified_date"],
            $row["comment_last_modified_by"],
        );
        unset($row['user']);
        $row['type'] = Translation::getTranslation($row['type']);
    }
}
