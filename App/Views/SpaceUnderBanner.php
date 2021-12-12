<?php

namespace App\Views;

use App\Entity\View\SpaceUnderBanner as ViewSpaceUnderBanner;
use Src\Theme\View;

class SpaceUnderBanner extends View
{

    public array $spaces;

    public function __construct()
    {
        $this->spaces = ViewSpaceUnderBanner::getAll(["is_hidden" => 0]);
    }

    public function getTemplateFile(): string
    {
        return "space_under_banner.twig";
    }
}
