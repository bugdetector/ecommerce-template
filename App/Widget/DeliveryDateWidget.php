<?php

namespace App\Widget;

use Src\Form\Widget\InputWidget;

class DeliveryDateWidget extends InputWidget
{

    public function __construct(string $name, string $startOf = "today")
    {
        parent::__construct($name);
        $this->addAttribute("id", "input_delivery_date")
        ->addAttribute("data-target", "#input_delivery_date")
        ->addAttribute("data-toggle", "datetimepicker")
        ->addAttribute("autocomplete", "off")
        ->addAttribute("data-start-of", date("Y-m-d", strtotime($startOf)))
        ->addClass("datetimepicker-input");
        $controller = \CoreDB::controller();
        $controller->addJsFiles("dist/datetimepicker/datetimepicker.js");
        $controller->addCssFiles("dist/datetimepicker/datetimepicker.css");
        $controller->addJsFiles("dist/delivery-date/delivery-date.js");
        $controller->addCssFiles("dist/delivery-date/delivery-date.css");
    }

    public function setValue($value)
    {
        return parent::setValue(
            $value ? date("d-m-Y", strtotime($value)) : null
        );
    }
}
