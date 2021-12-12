<?php

namespace App\Queries;

use App\Entity\Product\ProductPrice;
use CoreDB\Kernel\Database\SelectQueryPreparerAbstract;
use Src\Entity\Translation;
use Src\Entity\Variable;
use Src\Entity\ViewableQueries;
use Src\Views\TextElement;
use Src\Views\ViewGroup;

class ProductFinderQuery extends ViewableQueries
{
    public static function getInstance()
    {
        return parent::getByKey("product_finder_query");
    }

    public function getResultHeaders(bool $translateLabel = true): array
    {
        $headers = parent::getResultHeaders($translateLabel);
        $headers["name"] = Translation::getTranslation("category");
        unset($headers["ID"]);
        array_unshift($headers, "");
        $headers[] = Translation::getTranslation("price");
        return $headers;
    }

    public function getResultQuery(): SelectQueryPreparerAbstract
    {
        $query = parent::getResultQuery();
        $query->join(ProductPrice::getTableName(), "pp", "products.ID = pp.product");
        $currencyIcon = Variable::getByKey("currency_icon")->value->getValue();
        $query->selectWithFunction([
            "GROUP_CONCAT(pp.item_count, '+: {$currencyIcon}', pp.price SEPARATOR '\n') as price_info"
        ])->groupBy("products.ID");
        return $query;
    }

    public function postProcessRow(&$row): void
    {
        $row["ID"] = ViewGroup::create("button", "btn btn-sm btn-outline-primary")
        ->addClass("finder-select")
        ->addAttribute("value", $row["ID"])
        ->addField(
            TextElement::create(Translation::getTranslation("choose"))
        );
        $row["price_info"] = TextElement::create(
            nl2br($row["price_info"])
        )->setIsRaw(true)
        ->setTagName("div")
        ->addClass("text-nowrap");
    }
}
