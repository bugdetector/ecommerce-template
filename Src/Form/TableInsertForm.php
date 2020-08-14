<?php

namespace Src\Form;

use CoreDB\Kernel\TableMapper;
use Src\Entity\Translation;
use Src\Form\Widget\InputWidget;

class TableInsertForm extends Form
{
    public string $method = "POST";

    public bool $redirect = true;

    protected TableMapper $object;

    public function __construct(TableMapper $object)
    {
        parent::__construct();
        $this->object = $object;
        $this->setEnctype("multipart/form-data");
        \CoreDB::controller()->addJsFiles("src/js/insert.js");

        foreach($this->object->getFormFields($this->object->table) as $field){
            $this->addField($field);
        }
        $this->addField(
            InputWidget::create("save")
            ->addClass("btn btn-primary mt-2")
            ->setValue(Translation::getTranslation("save"))
            ->setType("submit")
        );
        if($this->object->ID){
            $this->addField(
                InputWidget::create("")
                ->addClass("btn btn-danger mt-2")
                ->setValue(Translation::getTranslation("delete"))
                ->setType("button")
                ->addClass("remove_accept")
            );
            $this->addField(
                InputWidget::create("delete")
                ->addClass("btn btn-danger mt-2")
                ->setType("submit")
                ->addAttribute("hidden", "true")
            );
        }
    }

    public function getFormId(): string
    {
        return "table_insert_form";
    }

    public function validate() : bool
    {
        return true;
    }

    public function submit()
    {
        if(isset($this->request["save"])){
            $success_message = $this->object->ID ? "update_success" : "insert_success";
            if(isset($this->request[$this->object->table])){
                $this->object->map($this->request[$this->object->table]);
            }
            $this->object->save();
            if(isset($_FILES[$this->object->table])){
                $this->object->include_files($_FILES[$this->object->table]);
            }
            $this->setMessage(Translation::getTranslation($success_message));
            if($this->redirect){
                \CoreDB::goTo($this->getSaveRedirectUrl());
            }
        }else if(isset($this->request["delete"])){
            $this->object->delete();
            $this->setMessage(Translation::getTranslation("record_removed"));
            if($this->redirect){
                \CoreDB::goTo($this->getDeleteRedirectUrl());
            }
        }
    }

    protected function getSaveRedirectUrl() :string {
        return BASE_URL."/admin/table/insert/{$this->object->table}/{$this->object->ID}";
    }

    protected function getDeleteRedirectUrl() :string {
        return BASE_URL."/admin/table/{$this->object->table}";
    }
}
