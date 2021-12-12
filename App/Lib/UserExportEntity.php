<?php

namespace App\Lib;

use App\Entity\CustomUser;

class UserExportEntity extends CustomUser
{
    public function postProcessRow(&$row): void
    {
        $row["registration_date"] = date("d-m-Y H:i:s", strtotime($row["registration_date"]));
        unset(
            $row["comment_last_modified_by"],
            $row["comment_last_modified_date"]
        );
    }
    public function getPaginationLimit(): int
    {
        return 0;
    }
}
