<?php

namespace App\AdminTheme;

use CoreDB;
use CoreDB\Kernel\BaseController;
use Src\Theme\ThemeInteface;

class EcommerceAdminController extends BaseController
{

    public function getTheme(): ThemeInteface
    {
        return new EcommerceAdminTheme();
    }

    public function checkAccess(): bool
    {
        return parent::checkAccess() || CoreDB::currentUser()->isUserInRole("Manager");
    }
}
