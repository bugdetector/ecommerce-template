<?php

namespace App\Entity;

use CoreDB\Kernel\Model;
use CoreDB\Kernel\Database\DataType\TableReference;

/**
 * Object relation with table authorized_branches
 * @author murat
 */

class AuthorizedBranch extends Model
{
    /**
    * @var TableReference $user
    * User reference.
    */
    public TableReference $user;
    /**
    * @var TableReference $branch
    * Branch reference.
    */
    public TableReference $branch;

    /**
     * @inheritdoc
     */
    public static function getTableName(): string
    {
        return "authorized_branches";
    }
}
