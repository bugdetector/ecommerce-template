<?php

namespace App\Controller;

use App\Entity\Basket\Basket;
use App\Entity\AppUser;
use App\Form\ShippingForm;
use App\Theme\AppController;
use Src\Entity\Translation;
use Src\Entity\Variable;

class ShippingController extends AppController
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
            $user = \CoreDB::currentUser();
            $user->shipping_option->setValue(AppUser::SHIPPING_OPTION_DELIVERY);
            $user->save();
            $this->setTitle(
                Translation::getTranslation("select_delivery_address")
            );
        } else {
            $this->setTitle(
                Translation::getTranslation("select_collection_or_delivery")
            );
        }
        $this->shippingForm = new ShippingForm();
        $this->shippingForm->processForm();
    }

    public function echoContent()
    {
        return $this->shippingForm;
    }
}
