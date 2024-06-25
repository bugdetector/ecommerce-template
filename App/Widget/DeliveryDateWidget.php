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
        ->addAttribute("data-start-of", date("d-m-Y", strtotime($startOf)))
        ->addClass("datetimepicker-input");
        $controller = \CoreDB::controller();
        $controller->addJsFiles("assets/js/components/datetimepicker.js");
        $controller->addCssFiles("assets/css/components/datetimepicker.css");
        $controller->addJsFiles("ecommerce_theme/src/components/delivery-date/delivery-date.js");
        $controller->addCssFiles("ecommerce_theme/src/components/delivery-date/delivery-date.css");
    }

    public function setValue($value)
    {
        return parent::setValue(
            $value ? date("d-m-Y", strtotime($value)) : null
        );
    }
}
