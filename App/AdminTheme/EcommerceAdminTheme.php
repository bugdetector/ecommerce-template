<?php

namespace App\AdminTheme;

use Src\BaseTheme\BaseTheme;

class EcommerceAdminTheme extends BaseTheme
{
    public static function getTemplateDirectories(): array
    {
        $directories = parent::getTemplateDirectories();
        array_unshift($directories, __DIR__ . "/templates");
        return $directories;
    }
}
