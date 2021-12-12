<?php

namespace App\Entity\Page;

use CoreDB\Kernel\Model;
use CoreDB\Kernel\Database\DataType\TableReference;
use CoreDB\Kernel\Database\DataType\File;

/**
 * Object relation with table page_attachments
 * @author murat
 */

class PageAttachment extends Model
{
    /**
    * @var TableReference $page
    * Page reference.
    */
    public TableReference $page;
    /**
    * @var File $attachment
    * Attachment file
    */
    public File $attachment;

    /**
     * @inheritdoc
     */
    public static function getTableName(): string
    {
        return "page_attachments";
    }
}
