<?php

namespace App\Views;

use Src\Theme\ResultsViewer;

class BlogCard extends ResultsViewer
{

    public function getTemplateFile(): string
    {
        return "blog-card.twig";
    }
}
