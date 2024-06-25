<?php

namespace App\Controller\Blogs;

use App\Controller\BlogsController;
use App\Entity\Blog;
use CoreDB\Kernel\Router;
use Src\Controller\NotFoundController;
use Src\Entity\File;
use Src\Views\Image;
use Src\Views\TextElement;
use Src\Views\ViewGroup;

class BlogController extends BlogsController
{
    public ?Blog $blog;

    public function preprocessPage()
    {
        $this->blog = Blog::getByUrlAlias(@$this->arguments[0]);
        if (!$this->blog) {
            Router::getInstance()->route(NotFoundController::getUrl());
        }
        $this->setTitle($this->blog->title);
    }

    public function getTemplateFile(): string
    {
        return "page-blog.twig";
    }

    public function echoContent()
    {
        $content = ViewGroup::create("div", "d-flex flex-column");
        if ($this->blog->cover_image->getValue()) {
            /** @var File */
            $coverImage = File::get($this->blog->cover_image->getValue());
            $content->addField(
                Image::create(
                    $coverImage->getUrl(),
                    $coverImage->file_name
                )->addClass("w-50 p-3 align-self-center")
            );
        }
        $content->addField(
            TextElement::create(
                $this->blog->content
            )->setIsRaw(true)
        );
        return $content;
    }
}
