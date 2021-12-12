<?php

namespace App\Widget;

use App\Entity\Postcode\Postcode;
use Src\Entity\Translation;
use Src\Form\Widget\FormWidget;
use Src\Form\Widget\SelectWidget;

class PostcodeWidget extends FormWidget
{

    public SelectWidget $postcodeSelect;
    public string $postcodeInputVal = "";

    public function __construct(string $name)
    {
        parent::__construct($name);
        $this->postcodeSelect = SelectWidget::create("")->setOptions(
            \CoreDB::database()->select(Postcode::getTableName(), "p")
                ->select("p", ["postcode", "postcode"])
                ->execute()->fetchAll(\PDO::FETCH_KEY_PAIR)
        )
        ->removeClass("selectpicker")
        ->setNullElement(Translation::getTranslation("please_choose"))
        ->addAttribute("required", "true")
        ->addClass("postcode-select");

        \CoreDB::controller()->addJsCode("
            $(function(){
                $(document).on(
                    'change, input',
                    '.postcode-widget .postcode-select, .postcode-widget .postcode-input',
                    function(){
                    let wrapper = $(this).parents('.postcode-widget');
                    let postcode = wrapper.find('.postcode-select').val() + ' ' +
                    wrapper.find('.postcode-input').val();
                    wrapper.find('.postcode-value').val(postcode);
                })
            })
        ");
    }

    public function setValue($value)
    {
        parent::setValue($value);
        $exploded = explode(" ", $value);
        $this->postcodeSelect->setValue(@$exploded[0] ?: "");
        $this->postcodeInputVal = @$exploded[1] ?: "";
    }

    public function getTemplateFile(): string
    {
        return "postcode-widget.twig";
    }

    public static function create(string $name): PostcodeWidget
    {
        return new PostcodeWidget($name);
    }
}
