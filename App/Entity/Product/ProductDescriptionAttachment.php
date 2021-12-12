<?php

namespace App\Entity\Product;

use CoreDB\Kernel\Model;
use CoreDB\Kernel\Database\DataType\TableReference;
use CoreDB\Kernel\Database\DataType\File;

/**
 * Object relation with table product_description_attachement
 * @author murat
 */

class ProductDescriptionAttachment extends Model
{
    /**
    * @var TableReference $product
    * Product reference.
    */
    public TableReference $product;
    /**
    * @var File $attachment
    * Attachment file.
    */
    public File $attachment;

    /**
     * @inheritdoc
     */
    public static function getTableName(): string
    {
        return "product_description_attachment";
    }
}
