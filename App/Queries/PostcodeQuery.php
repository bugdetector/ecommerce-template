<?php

namespace App\Queries;

use App\Entity\Postcode\Day;
use App\Entity\Postcode\Postcode;
use CoreDB\Kernel\Database\SelectQueryPreparerAbstract;
use Src\Entity\Translation;
use Src\Entity\ViewableQueries;
use Src\Form\Widget\FormWidget;
use Src\Views\TextElement;

class PostcodeQuery extends ViewableQueries
{
    public static function getInstance()
    {
        return self::getByKey("postcode");
    }

    public function getResultHeaders(bool $translateLabel = true): array
    {
        $headers = [
            "postcode" => Translation::getTranslation("postalcode"),
            "minimum_order_price" => Translation::getTranslation("minimum_order_price"),
            TextElement::create(
                Translation::getTranslation("delivery_charge") .
                "<span class='bg-warning rounded-circle mx-2 px-3 py-2 text-white'
                data-toggle='popover' title='" .
                Translation::getTranslation("delivery_charge")
                . "' data-content='" .
                Translation::getTranslation("delivery_charge_description")
                . "' data-trigger='hover'><i class='rounded fa fa-info'></i></span>"
            )->setIsRaw(true),
            "days" => Translation::getTranslation("delivery_days")
        ];
        return $headers;
    }

    public function getSearchFormFields(bool $translateLabel = true): array
    {
        $fields["postcode.postcode"] = null;
        /** @var FormWidget[] */
        $fields = array_merge($fields, parent::getSearchFormFields($translateLabel));
        $fields["postcode.postcode"] = $fields["postcode"]
        ->setName("postcode.postcode")
        ->setLabel(
            Translation::getTranslation("postalcode")
        );
        unset($fields["postcode"]);

        $fields["day"] = (new Postcode())->day->getWidget()
        ->setName("days[]")
        ->setLabel(
            Translation::getTranslation("delivery_days")
        );
        if (@$_GET["days"] && is_array($_GET["days"])) {
            $fields["day"]->setValue($_GET["days"]);
        }
        return $fields;
    }

    public function getResultQuery(): SelectQueryPreparerAbstract
    {
        $query = parent::getResultQuery();
        $query->selectWithFunction([
            "GROUP_CONCAT(d.day SEPARATOR ', ') AS days"
        ])
        ->join("postcode_days", "pd", "pd.postcode = postcode.ID")
        ->join(Day::getTableName(), "d", "d.ID = pd.day")
        ->groupBy("postcode.ID");
        if (@$_GET["days"] && is_array($_GET["days"])) {
            $query->condition("d.ID", $_GET["days"], "IN");
        }
        return $query;
    }
}
