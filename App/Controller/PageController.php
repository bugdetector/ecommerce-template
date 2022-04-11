<?php

namespace App\Controller;

use App\Entity\Page\Page;
use App\Theme\AppController;
use CoreDB\Kernel\Router;
use Src\Controller\NotFoundController;

class PageController extends AppController
{

    public ?Page $page;

    public function __construct(array $arguments)
    {
        parent::__construct($arguments);
        $this->page = Page::get(["url_alias" => @$this->arguments[0]]);
        if (!$this->page) {
            Router::getInstance()->route(NotFoundController::getUrl());
        }
        $this->setTitle(
            $this->page->title
        );
    }

    public function checkAccess(): bool
    {
        return true;
    }

    public function echoContent()
    {
        return $this->page->body;
    }
}
