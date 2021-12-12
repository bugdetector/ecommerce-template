<?php

namespace App\Queries;

use CoreDB\Kernel\Database\SelectQueryPreparerAbstract;
use Src\Entity\Translation;
use Src\Entity\ViewableQueries;
use Src\Views\TextElement;
use Src\Views\ViewGroup;

class AccountFinderQuery extends ViewableQueries
{
    public static function getInstance()
    {
        return parent::getByKey("account_finder_query");
    }

    public function getResultHeaders(bool $translateLabel = true): array
    {
        $headers = parent::getResultHeaders($translateLabel);
        return $headers;
    }

    public function getResultQuery(): SelectQueryPreparerAbstract
    {
        $query = parent::getResultQuery();
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
    }
}
