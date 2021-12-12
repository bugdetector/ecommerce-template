<?php

namespace App\Entity\View;

use App\Controller\AdminController;
use CoreDB\Kernel\Database\DataType\Checkbox;
use CoreDB\Kernel\Model;
use CoreDB\Kernel\Database\DataType\File;
use CoreDB\Kernel\Database\DataType\Integer;
use CoreDB\Kernel\Database\DataType\LongText;
use CoreDB\Kernel\Database\DataType\ShortText;
use Src\Entity\TreeEntityAbstract;
use Src\Theme\View;

/**
 * Object relation with table space_under_banner
 * @author murat
 */

class SpaceUnderBanner extends TreeEntityAbstract
{
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
    * @var ShortText $text
    * HTML text
    */
    public ShortText $text;
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


    public static function getAll(array $filter): array
    {
        return static::findAll($filter, static::getTableName(), "weight");
    }

    /**
     * @inheritdoc
     */
    public static function getTableName(): string
    {
        return "space_under_banner";
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
        return AdminController::getUrl() . "space_under_banner/" . ($value ?: $this->ID);
    }

    protected function getFieldWidget(string $field_name, bool $translateLabel): ?View
    {
        if ($field_name == "weight") {
            return null;
        } else {
            return parent::getFieldWidget($field_name, $translateLabel);
        }
    }
}
