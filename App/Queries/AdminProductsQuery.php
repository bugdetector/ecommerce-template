<?php

namespace App\Queries;

use App\Controller\Admin\Products\InsertController;
use CoreDB\Kernel\Database\SelectQueryPreparerAbstract;
use Src\Entity\Translation;
use Src\Entity\ViewableQueries;
use Src\Form\Widget\SelectWidget;
use Src\Views\Link;
use Src\Views\ViewGroup;

class AdminProductsQuery extends ViewableQueries
{
    public static function getInstance()
    {
        return parent::getByKey("admin_product_list");
    }

    public function postProcessRow(&$row): void
    {
        $row["ID"] = Link::create(
            InsertController::getUrl() . $row["ID"],
            ViewGroup::create("i", "fa fa-edit text-primary core-control")
        );
        $row["published"] = $row["published"] ? Translation::getTranslation("published") : null;
        $row["exclude_stock"] = $row["exclude_stock"] ?
        Translation::getTranslation("exclude_stock") : null;
        unset($row["is_special_product"]);
    }
    public function getSearchFormFields(bool $translateLabel = true): array
    {
        $fields = parent::getSearchFormFields($translateLabel);
        $fields[] = SelectWidget::create("minimum_order_count")
        ->setLabel(Translation::getTranslation("minimum_order_count"))
        ->setOptions([
            "set" => Translation::getTranslation("set"),
            "not_set" => Translation::getTranslation("not_set"),
        ]);
        $fields[] = SelectWidget::create("maximum_order_count")
        ->setLabel(Translation::getTranslation("maximum_order_count"))
        ->setOptions([
            "set" => Translation::getTranslation("set"),
            "not_set" => Translation::getTranslation("not_set"),
        ]);

        return $fields;
    }

    public function getResultQuery(): SelectQueryPreparerAbstract
    {
        $query = parent::getResultQuery();
        if (isset($_GET["minimum_order_count"]) && $_GET["minimum_order_count"]) {
            if ($_GET["minimum_order_count"] == "set") {
                $query->condition("minimum_order_count", 0, ">");
            } elseif ($_GET["minimum_order_count"] == "not_set") {
                $query->condition("minimum_order_count", 0);
            }
            unset($_GET["minimum_order_count"]);
        }
        if (isset($_GET["maximum_order_count"]) && $_GET["maximum_order_count"]) {
            if ($_GET["maximum_order_count"] == "set") {
                $query->condition("maximum_order_count", 0, ">");
            } elseif ($_GET["maximum_order_count"] == "not_set") {
                $query->condition("maximum_order_count", 0);
            }
            unset($_GET["maximum_order_count"]);
        }
        return $query;
    }
    public function getResultHeaders(bool $translateLabel = true): array
    {
        $headers = parent::getResultHeaders($translateLabel);
        unset($headers["is_special_product"]);
        return $headers;
    }
}
