<?php

namespace App\AdminTheme;

use Src\BaseTheme\BaseTheme;

<<<<<<<< HEAD:App/AdminTheme/EcommerceAdminTheme.php
class EcommerceAdminTheme extends BaseTheme
========
class AppTheme extends BaseTheme
>>>>>>>> d4c57990592c1dc7bc0fee8281fdedbe5e06d5dc:App/Theme/AppTheme.php
{
    public static function getTemplateDirectories(): array
    {
        $directories = parent::getTemplateDirectories();
        array_unshift($directories, __DIR__ . "/templates");
        return $directories;
    }
}
