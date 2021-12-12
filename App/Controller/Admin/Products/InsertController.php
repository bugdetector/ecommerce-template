<?php

namespace App\Controller\Admin\Products;

use App\Controller\Admin\ProductsController;
use App\Entity\Product\Product;
use CoreDB\Kernel\Router;
use Src\Controller\NotFoundController;
use Src\Entity\Translation;
use Src\Form\InsertForm;

class InsertController extends ProductsController
{

    public ?Product $product;
    public InsertForm $productInsertForm;

    public function preprocessPage()
    {
        if (isset($this->arguments[0]) && $this->arguments[0]) {
            $this->product = Product::get($this->arguments[0]);
            if (!$this->product) {
                Router::getInstance()->route(NotFoundController::getUrl());
            }
            $title = Translation::getTranslation("edit") . " | " . $this->product->title;
        } else {
            $this->product = new Product();
            $title = Translation::getTranslation("add_new_product");
        }
        $this->setTitle($title);
        $this->productInsertForm = $this->product->getForm();
        $this->productInsertForm->processForm();
        $this->addJsFiles("dist/file_input/file_input.js");
        $this->addCssFiles("dist/file_input/file_input.css");
        $this->addFrontendTranslation("close");
        $this->productInsertForm->addClass("p-3");
    }

    public function echoContent()
    {
        return $this->productInsertForm;
    }
}
