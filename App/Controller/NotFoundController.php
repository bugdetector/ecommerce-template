<?php

namespace App\Controller;

use Src\Controller\NotFoundController as ControllerNotFoundController;

class NotFoundController extends ControllerNotFoundController
{
    public function __construct()
    {
        \CoreDB::goTo(BASE_URL);
    }
}
