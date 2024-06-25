<?php

namespace App\Views;

use App\Entity\View\MainpageBox;
use Src\Theme\View;

class MainpageBoxes extends View
{
    public array $boxes;

    public function __construct(string $place = MainpageBox::PLACE_UNDER_LATEST_OFFERS)
    {
        $this->boxes = MainpageBox::getBoxesInPlace($place);
    }

    public function getTemplateFile(): string
    {
        return "mainpage-boxes.twig";
    }
}
