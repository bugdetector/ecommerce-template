<?php

namespace App\Controller;

use App\Entity\Basket\Basket;
use App\Form\ShippingForm;
use App\Theme\CustomTheme;
use Src\Entity\Translation;
use Src\Entity\Variable;

class ShippingController extends CustomTheme
{

    public ShippingForm $shippingForm;

    public function checkAccess(): bool
    {
        $currentUser = \CoreDB::currentUser();
        return $currentUser->isLoggedIn();
    }

    public function preprocessPage()
    {
        if (!Variable::getByKey("collection_order_enabled")->value->getValue()) {
            $userBasket = Basket::getUserBasket();
            $userBasket->type->setValue(Basket::TYPE_DELIVERY);
            $userBasket->save();
            \CoreDB::goTo(
                @$_SERVER["HTTP_REFERER"] ?: MainpageController::getUrl()
            );
        }
        $this->setTitle(
            Translation::getTranslation("select_collection_or_delivery")
        );
        $this->shippingForm = new ShippingForm();
        $this->shippingForm->processForm();
    }

    public function echoContent()
    {
        return $this->shippingForm;
    }
}
