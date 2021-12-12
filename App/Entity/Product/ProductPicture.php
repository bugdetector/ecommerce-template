<?php

namespace App\Entity\Product;

use CoreDB\Kernel\Model;
use CoreDB\Kernel\Database\DataType\TableReference;
use CoreDB\Kernel\Database\DataType\File;

/**
 * Object relation with table product_pictures
 * @author robokopiye
 */

class ProductPicture extends Model
{
    /**
    * @var TableReference $product
    * product info
    */
    public TableReference $product;
    /**
    * @var File $image
    * product image
    */
    public File $image;

    /**
     * @inheritdoc
     */
    public static function getTableName(): string
    {
        return "product_pictures";
    }
}
