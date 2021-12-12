<?php

namespace App\Entity\Postcode;

use CoreDB\Kernel\Model;
use CoreDB\Kernel\Database\DataType\ShortText;

/**
 * Object relation with table days
 * @author murat
 */

class Day extends Model
{
    /**
    * @var ShortText $day
    * Day name.
    */
    public ShortText $day;

    /**
     * @inheritdoc
     */
    public static function getTableName(): string
    {
        return "days";
    }
}
