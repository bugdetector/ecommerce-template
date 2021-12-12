<?php

namespace App\Entity\View;

use CoreDB\Kernel\Model;
use CoreDB\Kernel\Database\DataType\Checkbox;
use CoreDB\Kernel\Database\DataType\EnumaratedList;
use CoreDB\Kernel\Database\DataType\TableReference;
use CoreDB\Kernel\Database\DataType\ShortText;
use CoreDB\Kernel\Database\DataType\LongText;
use Src\Form\Widget\SelectWidget;
use Src\Theme\View;

/**
 * Object relation with table banner_box
 * @author murat
 */

class BannerBox extends Model
{
     /**
    * POSITION_LEFT description.
    */
    public const POSITION_LEFT = "left";
    /**
    * POSITION_RIGHT description.
    */
    public const POSITION_RIGHT = "right";
    /**
    * POSITION_CENTER description.
    */
    public const POSITION_CENTER = "center";

    /**
    * @var Checkbox $active
    * If checked this record shown on banner, If not checked not shown.
    */
    public Checkbox $active;
    /**
    * @var EnumaratedList $position
    * Position of card.
    */
    public EnumaratedList $position;
    /**
    * @var TableReference $banner
    *
    */
    public TableReference $banner;
    /**
    * @var ShortText $title
    * Title of record
    */
    public ShortText $title;
    /**
    * @var LongText $text
    * HTML text
    */
    public LongText $text;
    /**
    * @var ShortText $line_color
    * Line color of this box.
    */
    public ShortText $line_color;
    /**
    * @var ShortText $url
    * Url to redirect when click.
    */
    public ShortText $url;
    /**
    * @var ShortText $button_text
    * If empty "Find out more" will be applied.
    */
    public ShortText $button_text;
    /**
    * @var ShortText $button_color
    * Button color.
    */
    public ShortText $button_color;

    /**
     * @inheritdoc
     */
    public static function getTableName(): string
    {
        return "banner_box";
    }

    protected function getFieldWidget(string $field_name, bool $translateLabel): ?View
    {
        if (in_array($field_name, ["line_color", "background_color", "button_color"])) {
            /** @var InputWidget */
            $widget = parent::getFieldWidget($field_name, $translateLabel);
            $widget->setType("color")
            ->removeClass("form-control");
            return $widget;
        } elseif ($field_name == "position") {
            /** @var SelectWidget */
            $widget = parent::getFieldWidget($field_name, $translateLabel);
            $widget->setNullElement(null);
            return $widget;
        } else {
            return parent::getFieldWidget($field_name, $translateLabel);
        }
    }
}
