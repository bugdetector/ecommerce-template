<?php

namespace App\Views;

use Src\Views\ViewGroup;

class GraphView extends ViewGroup
{
    public $id;

    public function __construct(string $tag_name, string $wrapper_class)
    {
        parent::__construct($tag_name, $wrapper_class);
        $this->id = bin2hex(random_bytes(10));
        $this->addField(
            ViewGroup::create("canvas", "")
            ->addAttribute("id", $this->id)
        );
        $this->addClass("graph-view");
        $controller = \CoreDB::controller();
        $controller->addJsFiles("dist/graph_view/graph_view.js");
    }

    public function setDataServiceUrl($dataServiceUrl): GraphView
    {
        $this->addAttribute("data-service-url", $dataServiceUrl);
        return $this;
    }

    public static function create(string $tag_name, string $wrapper_class): GraphView
    {
        return new GraphView($tag_name, $wrapper_class);
    }
}
