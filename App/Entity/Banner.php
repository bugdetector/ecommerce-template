<?php

namespace App\Entity;

use App\Controller\Admin\AjaxController;
use App\Controller\Admin\Banner\InsertController;
use CoreDB\Kernel\Database\DataType\File;
use CoreDB\Kernel\Database\DataType\Integer;
use CoreDB\Kernel\Database\DataType\ShortText;
use CoreDB\Kernel\EntityReference;
use Src\Entity\TreeEntityAbstract;

/**
 * Object relation with table banner
 * @author murat
 */

class Banner extends TreeEntityAbstract
{
    /**
    * @var ShortText $title
    * Banner title.
    */
    public ShortText $title;
    /**
    * @var File $desktop_image
    * Image that shown on desktop.
    */
    public File $desktop_image;
    /**
    * @var File $mobile_image
    * Image that shown on mobile.
    */
    public File $mobile_image;
   /**
    * @var ShortText $url
    * Url that banner redirects when clicked. Leave empty if you want to use banner product list.
    */
    public ShortText $url;
    /**
    * @var Integer $weight
    *
    */
    public Integer $weight;
    public EntityReference $banner_box;
    public EntityReference $banner_product;

    /**
     * @inheritdoc
     */
    public static function getTableName(): string
    {
        return "banner";
    }

    public function getFormFields($name, bool $translateLabel = true): array
    {
        $fields = parent::getFormFields($name, $translateLabel);
        unset($fields["weight"]);
        return $fields;
    }

    public static function hasSubItems()
    {
        return false;
    }

    public function getRemoveServiceUrl(): string
    {
        return AjaxController::getUrl() . "removeBanner";
    }

    public static function getTreeFieldName(): string
    {
        return "title";
    }

    public function editUrl($value = null)
    {
        return InsertController::getUrl() . ($value ?: $this->ID->getValue());
    }
}
