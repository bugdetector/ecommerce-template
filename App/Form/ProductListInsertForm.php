<?php

namespace App\Form;

use App\Controller\Admin\ProductlistsController;
use App\Entity\Product\ProductList;
use CoreDB\Kernel\Model;
use Src\Form\InsertForm;

class ProductListInsertForm extends InsertForm
{
    /** @var ProductList $object */
    protected Model $object;

    protected function submitSuccess()
    {
        \CoreDB::goTo(
            ProductlistsController::getUrl() . $this->object->getTargetList()
        );
    }

    protected function deleteSuccess(): string
    {
        \CoreDB::goTo(
            ProductlistsController::getUrl() . $this->object->getTargetList()
        );
    }
}
