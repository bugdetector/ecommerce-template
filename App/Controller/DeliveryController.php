<?php

namespace App\Controller;

use App\Queries\PostcodeQuery;
use App\Theme\AppController;
use CoreDB\Kernel\Messenger;
use Src\Entity\Translation;
use Src\Entity\Variable;
use Src\Form\SearchForm;

class DeliveryController extends AppController
{
    public SearchForm $postcodeSearch;

    public function checkAccess(): bool
    {
        return (bool) Variable::getByKey("use_postcode_list")->value->getValue();
    }

    public function preprocessPage()
    {
        $postcodeQuery = PostcodeQuery::getInstance();
        $this->postcodeSearch = SearchForm::createByObject($postcodeQuery);
        $this->setTitle(
            Translation::getTranslation("delivery_days")
        );
        $this->createMessage(
            Translation::getTranslation("postcode_table_info"),
            Messenger::INFO
        );
    }

    public function echoContent()
    {
        return $this->postcodeSearch;
    }
}
