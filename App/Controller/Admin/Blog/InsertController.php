<?php

namespace App\Controller\Admin\Blog;

use App\Controller\Admin\BlogController;
use App\Entity\Blog;
use CoreDB\Kernel\Router;
use Src\Controller\NotFoundController;
use Src\Entity\Translation;
use Src\Form\InsertForm;

class InsertController extends BlogController
{
    public ?Blog $blog;
    public InsertForm $blogInsertForm;

    public function preprocessPage()
    {
        if (isset($this->arguments[0]) && $this->arguments[0]) {
            $this->blog = Blog::get($this->arguments[0]);
            if (!$this->blog) {
                Router::getInstance()->route(NotFoundController::getUrl());
            }
            $title = Translation::getTranslation("edit") . " | " . $this->blog->title;
        } else {
            $this->blog = new Blog();
            $title = Translation::getTranslation("add_new_blog");
        }
        $this->setTitle($title);
        $this->blogInsertForm = $this->blog->getForm();
        $this->blogInsertForm->processForm();
        $this->blogInsertForm->addClass("p-3");
    }

    public function echoContent()
    {
        return $this->blogInsertForm;
    }
}
