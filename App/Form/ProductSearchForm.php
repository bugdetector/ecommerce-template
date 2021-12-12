<?php

namespace App\Form;

use CoreDB\Kernel\SearchableInterface;
use Src\Form\SearchForm;

class ProductSearchForm extends SearchForm
{
    public string $search;
    public string $listPlace = "product_list";

    protected function __construct(SearchableInterface $object, $translateLabels = true)
    {
        parent::__construct($object, $translateLabels);
        $this->search = @$_GET["search"] ?: "";
        $this->addClass("product-list-container");
    }

    public function getTemplateFile(): string
    {
        return "product-search-form.twig";
    }

    /**
     * @return string
     * Set tag for product tracker analytics
     */
    public function setListPlace(string $listPlace)
    {
        $this->listPlace = $listPlace;
    }

    /**
     * @return string
     * Return tag for product tracker analytics
     */
    public function getListPlace(): string
    {
        return $this->listPlace;
    }
    public function render()
    {
        $this->addAttribute("data-place", $this->getListPlace());
        return parent::render();
    }
}
