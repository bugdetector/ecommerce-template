<?php

namespace App\Controller;

use App\Form\ProfileForm;
use App\Theme\CustomTheme;

class ProfileController extends CustomTheme
{
    public ProfileForm $profileForm;

    public function preprocessPage()
    {
        $user = \CoreDB::currentUser();
        $this->setTitle(
            \CoreDB::currentUser()->address->getValue()[0]["company_name"]
        );
        $this->profileForm = new ProfileForm();
        $this->profileForm->processForm();
        $this->profileForm->addClass("p-3");

        if (@$_GET["add-address"] == "true") {
            $this->addJsCode(
                "$(function($){
                    $('.add-new-entity[data-entity=\"additional_delivery_address\"]').click();
                    $([document.documentElement, document.body]).animate({
                        scrollTop: $('.add-new-entity[data-entity=\"additional_delivery_address\"]').offset().top
                    }, 500);
                })"
            );
        }
    }

    public function echoContent()
    {
        return $this->profileForm;
    }
}
