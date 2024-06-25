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
        $user = CoreDB::currentUser();
        return $user->isAdmin() || $user->isUserInRole('Manager');
    }
}
