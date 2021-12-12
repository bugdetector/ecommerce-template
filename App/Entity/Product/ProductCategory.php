<?php

namespace App\Entity\Product;

use App\Controller\Admin\AjaxController;
use App\Controller\Admin\CategoryController;
use CoreDB\Kernel\Database\DataType\File;
use Src\Entity\TreeEntityAbstract;
use CoreDB\Kernel\Database\DataType\ShortText;
use CoreDB\Kernel\Database\DataType\TableReference;
use CoreDB\Kernel\Database\DataType\Integer;
use Src\Theme\View;

/**
 * Object relation with table product_categories
 * @author murat
 */

class ProductCategory extends TreeEntityAbstract
{
    /**
    * @var ShortText $name
    * Name of category.
    */
    public ShortText $name;
    /**
    * @var ShortText $code
    * Temporary column for migration.
    */
    public ShortText $code;
    /* @var File $image
    * Category box image.
    */
    public File $image;
    /**
    * @var TableReference $parent
    * Parent category value. If needed.
    */
    public TableReference $parent;
    /**
    * @var Integer $weight
    * Weight used when sorting categories.
    */
    public Integer $weight;
    /**
     * @inheritdoc
     */
    public static function getTableName(): string
    {
        return "product_categories";
    }

    public static function getTreeFieldName(): string
    {
        return "name";
    }

    public function getRemoveServiceUrl(): string
    {
        return AjaxController::getUrl() . "removeCategory";
    }

    public function editUrl($value = null)
    {
        return CategoryController::getUrl() . ($value ?: $this->ID);
    }

    protected function getFieldWidget(string $field_name, bool $translateLabel): ?View
    {
        if (in_array($field_name, ["parent", "weight"])) {
            return null;
        } else {
            return parent::getFieldWidget($field_name, $translateLabel);
        }
    }
}
