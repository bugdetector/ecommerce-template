<?php

namespace App\Entity;

/**
 * Object relation with table custom_user_address
 * @author murat
 */

class UserAdditionalAddress extends UserAddress
{

    public static function get($filter, $isDefault = true)
    {
        @$filter["default"] = 0;
        return parent::get($filter);
    }
    public static function getAll(array $filter, $isDefault = true): array
    {
        $filter["default"] = 0;
        return parent::getAll($filter);
    }

    public function save()
    {
        $this->default->setValue(0);
        return parent::save();
    }
}
