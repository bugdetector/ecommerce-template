<?php

namespace App\Entity;

use CoreDB\Kernel\Model;
use CoreDB\Kernel\Database\DataType\TableReference;
use CoreDB\Kernel\Database\DataType\File;

/**
 * Object relation with table blog_attachments
 * @author murat
 */

class BlogAttachment extends Model
{
    /**
    * @var TableReference $blog
    * Blog reference.
    */
    public TableReference $blog;
    /**
    * @var File $attachment
    * Attachment.
    */
    public File $attachment;

    /**
     * @inheritdoc
     */
    public static function getTableName(): string
    {
        return "blog_attachments";
    }
}
