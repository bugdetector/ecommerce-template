<?php

namespace App\AdminTheme;

use CoreDB\Kernel\ControllerInterface;
use Src\BaseTheme\BaseTheme;

class EcommerceAdminTheme extends BaseTheme
{

    public static function getTemplateDirectories(): array
    {
        $directories = parent::getTemplateDirectories();
        array_unshift($directories, __DIR__ . "/templates");
        return $directories;
    }

    protected function addDefaultJsFiles(ControllerInterface $controller)
    {
        parent::addDefaultJsFiles($controller);
        $controller->addJsFiles("dist/csl_global/csl_global.js");
    }

    protected function addDefaultCssFiles(ControllerInterface $controller)
    {
        parent::addDefaultCssFiles($controller);
        $controller->addCssFiles("dist/csl_global/csl_global.css");
    }
}
