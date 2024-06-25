<?php

namespace App\Theme;

use App\Controller\ShippingController;
use App\Controller\WelcomeController;
use App\Entity\AppUser;
use CoreDB\Kernel\BaseController;
use CoreDB\Kernel\Messenger;
use Src\Entity\Translation;
use Src\Theme\ThemeInteface;

class AppController extends BaseController
{
    public function getTheme(): ThemeInteface
    {
        return new AppTheme();
    }

    public function checkAccess(): bool
    {
        /** @var AppUser */
        $user = \CoreDB::currentUser();
        if (!$user->email_verified->getValue()) {
            \CoreDB::goTo(WelcomeController::getUrl());
        }
        if (!$user->shipping_option->getValue()) {
            \CoreDB::goTo(ShippingController::getUrl());
        }
        $deliveryDate = date("Y-m-d", @strtotime($user->delivery_date->getValue()));
        if (
            $user->shipping_option->getValue() == AppUser::SHIPPING_OPTION_COLLECTION &&
            strtotime($deliveryDate . " 18:00") < strtotime("now")
        ) {
            $this->createMessage(
                Translation::getTranslation(
                    "please_select_date"
                ),
                Messenger::WARNING
            );
            \CoreDB::goTo(ShippingController::getUrl());
        }
        return $user->isLoggedIn();
    }
}
