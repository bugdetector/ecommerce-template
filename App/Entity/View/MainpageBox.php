<?php

namespace App\Entity\View;

use App\Controller\Admin\BoxController;
use CoreDB\Kernel\Database\DataType\Checkbox;
use CoreDB\Kernel\Database\DataType\EnumaratedList;
use CoreDB\Kernel\Database\DataType\File;
use CoreDB\Kernel\Database\DataType\ShortText;
use CoreDB\Kernel\Database\DataType\Integer;
use CoreDB\Kernel\Database\DataType\LongText;
use Src\Entity\TreeEntityAbstract;
use Src\Form\Widget\InputWidget;
use Src\Theme\View;

/**
 * Object relation with table mainpage_boxes
 * @author murat
 */

class MainpageBox extends TreeEntityAbstract
{
    /**
    * PLACE_UNDER_LATEST_OFFERS description.
    */
    public const PLACE_UNDER_LATEST_OFFERS = "under_latest_offers";
    /**
    * PLACE_UNDER_TOP_SELLERS description.
    */
    public const PLACE_UNDER_TOP_SELLERS = "under_top_sellers";
    /**
    * SIZE_COL-MD-2 description.
    */
    public const SIZE_COL_MD_2 = "col_md_2";
    /**
    * SIZE_COL-MD-3 description.
    */
    public const SIZE_COL_MD_3 = "col_md_3";
    /**
    * SIZE_COL-MD-4 description.
    */
    public const SIZE_COL_MD_4 = "col_md_4";
    /**
    * SIZE_COL-MD-6 description.
    */
    public const SIZE_COL_MD_6 = "col_md_6";

    /**
    * @var ShortText $title
    * Title of record
    */
    public ShortText $title;
    /**
    * @var File $image
    * Image (required)
    */
    public File $image;
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
    * @var File $attachment
    * Attachment file (optional)
    */
    public File $attachment;
    /**
    * @var Integer $weight
    * Order weight.
    */
    public Integer $weight;
    /**
    * @var Checkbox $is_hidden
    * If checked this record not shown on mainpage.
    */
    public Checkbox $is_hidden;
    /**
    * @var ShortText $button_text
    * If empty "Find out more" will be applied.
    */
    public ShortText $button_text;
    /**
    * @var EnumaratedList $place
    * Place will be used for card placement in mainpage.
    */
    public EnumaratedList $place;
    /**
    * @var EnumaratedList $size
    * Size will be applied as card size.
    */
    public EnumaratedList $size;

    /**
     * @inheritdoc
     */

    public function __construct(string $tableName = null, array $mapData = [])
    {
        parent::__construct($tableName, $mapData);
        if (!$this->line_color->getValue()) {
            $this->line_color->setValue("#8c93d3bd");
        }
    }
    /**
     * @inheritdoc
     */

    public static function getAll(array $filter): array
    {
        return static::findAll($filter, static::getTableName(), "weight");
    }

    public static function getBoxesInPlace(string $place): array
    {
        return static::findAll([
            "place" => $place,
            "is_hidden" => 0
        ], static::getTableName(), "weight");
    }

    /**
     * @inheritdoc
     */
    public static function getTableName(): string
    {
        return "mainpage_boxes";
    }

    public static function hasSubItems()
    {
        return false;
    }

    public static function getTreeFieldName(): string
    {
        return "title";
    }

    public function getRemoveServiceUrl(): string
    {
        return "";
    }

    public function editUrl($value = null)
    {
        return BoxController::getUrl() . ($value ?: $this->ID);
    }

    protected function getFieldWidget(string $field_name, bool $translateLabel): ?View
    {
        if ($field_name == "weight") {
            return null;
        } elseif ($field_name == "line_color") {
            /** @var InputWidget */
            $widget = parent::getFieldWidget($field_name, $translateLabel);
            $widget->setType("color")
            ->removeClass("form-control");
            return $widget;
        } else {
            return parent::getFieldWidget($field_name, $translateLabel);
        }
    }
}
