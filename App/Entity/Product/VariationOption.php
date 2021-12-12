<?php

namespace App\Entity\Product;

use CoreDB\Kernel\Database\DataType\ShortText;
use CoreDB\Kernel\Database\DataType\Integer;
use Src\Entity\TreeEntityAbstract;
use Src\Form\Widget\SelectWidget;

/**
 * Object relation with table variation_options
 * @author murat
 */

class VariationOption extends TreeEntityAbstract
{
    /**
    * @var ShortText $title
    * Variation name shown on selectbox.
    */
    public ShortText $title;
    /**
    * @var Integer $weight
    *
    */
    public Integer $weight;

    private static array $variationOptions = [];
    /**
     * @inheritdoc
     */
    public static function getTableName(): string
    {
        return "variation_options";
    }

    public static function getTreeFieldName(): string
    {
        return "title";
    }

    public function getRemoveServiceUrl(): string
    {
        return "";
    }

    public static function hasSubItems()
    {
        return false;
    }

    public static function getSelectField(): SelectWidget
    {
        if (!self::$variationOptions) {
            $options = [];
            foreach (self::getRootElements() as $variation) {
                $options[$variation->ID->getValue()] = $variation->title->getValue();
            }
            self::$variationOptions = $options;
        }
        $widget = new SelectWidget("");
        $widget->setOptions(self::$variationOptions)
            ->setNullElement(null);
        return $widget;
    }
}
