<?php

namespace App\Controller\Admin\Branch;

use App\Controller\Admin\BranchController;
use App\Entity\Branch;
use CoreDB\Kernel\Router;
use Src\Controller\NotFoundController;

class InsertController extends BranchController
{
    public ?Branch $branch;

    public function preprocessPage()
    {
        if (@$this->arguments[0]) {
            $this->branch = Branch::get($this->arguments[0]);
            if (!$this->branch) {
                Router::getInstance()->route(NotFoundController::getUrl());
            }
        } else {
            $this->branch = new Branch();
        }
        $this->branchForm = $this->branch->getForm();
        $this->branchForm->processForm();
    }
}
