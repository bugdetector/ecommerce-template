<?php

namespace App\Views;

use Src\Entity\Translation;
use Src\Views\TextElement;
use Src\Views\ViewGroup;

class TotalOrdersGraphFilter extends ViewGroup
{

    public const FILTERS = [
        "yearly",
        "monthly",
        "weekly",
        "daily",
        "hourly",
        //"custom"
    ];

    public function __construct(string $tag_name, string $wrapper_class)
    {
        parent::__construct($tag_name, $wrapper_class);
        $buttonGroup = ViewGroup::create("div", "btn-group btn-group-toggle")
        ->addAttribute("id", "totals_filter");
        foreach (self::FILTERS as $filter) {
            $buttonGroup->addField(
                TextElement::create(
                    "<input type='radio' name='totals_filter' id='filter_{$filter}' value='{$filter}'" .
                    ($filter == 'monthly' ? 'checked' : '') . " class='btn-check'>
                    " . Translation::getTranslation($filter) . ""
                )->setIsRaw(true)
                ->setTagName("label")
                ->addClass("btn btn-outline-dark")
                ->addClass($filter == 'monthly' ? 'active' : '')
                ->addAttribute("for", "filter_{$filter}")
            );
        }
        $this->addField(
            $buttonGroup
        );

        \CoreDB::controller()->addJsFiles("dist/totals_filter/totals_filter.js");
    }

    public static function create(string $tag_name, string $wrapper_class): ViewGroup
    {
        return new TotalOrdersGraphFilter($tag_name, $wrapper_class);
    }
}
