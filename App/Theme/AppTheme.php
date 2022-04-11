<?php

namespace App\Theme;

use CoreDB\Kernel\ControllerInterface;
use Src\BaseTheme\BaseTheme;
use Src\Entity\Variable;
use Src\Views\Navbar;

class AppTheme extends BaseTheme
{
    public ?Navbar $categoryNavbar;

    public static function getTemplateDirectories(): array
    {
        $directories = parent::getTemplateDirectories();
        array_unshift($directories, __DIR__ . "/templates");
        return $directories;
    }

    protected function addDefaultJsFiles(ControllerInterface $controller)
    {
        parent::addDefaultJsFiles($controller);
        $controller->addJsFiles("dist/basket/basket.js");
        $controller->addJsFiles("dist/csl_global/csl_global.js");
        $controller->addJsFiles("dist/navbar-search/navbar-search.js");
        $tagManagerId = Variable::getByKey("google_tag_manager_id")->value->getValue();
        $controller->addJsFiles("https://www.googletagmanager.com/gtag/js?id={$tagManagerId}");
        $controller->addJsCode(
            "window.dataLayer = window.dataLayer || [];
            function gtag(){dataLayer.push(arguments);}
            gtag('js', new Date());
            gtag('config', '{$tagManagerId}');"
        );
    }

    protected function addDefaultCssFiles(ControllerInterface $controller)
    {
        parent::addDefaultCssFiles($controller);
        $controller->addCssFiles("dist/basket/basket.css");
        $controller->addCssFiles("dist/csl_global/csl_global.css");
        $controller->addCssFiles("dist/navbar-search/navbar-search.css");
    }

    protected function addDefaultTranslations(ControllerInterface $controller)
    {
        parent::addDefaultTranslations($controller);
        $controller->addFrontendTranslation("record_remove_accept");
        $controller->addFrontendTranslation("add_to_basket");
        $controller->addFrontendTranslation("please_enter_quantity");
        $controller->addFrontendTranslation("added_to_basket");
        if (!@$_COOKIE["accepted"]) {
            $controller->addFrontendTranslation("cookie_accept_message");
            $controller->addFrontendTranslation("allow_cookies");
            $controller->addJsCode(
                '$(
                    function(){
                        var cookieToast = toastr.info( 
                            _t("cookie_accept_message") , 
                            _t("allow_cookies"), 
                            {
                                timeOut: 0,
                                extendedTimeOut: 0,
                                tapToDismiss: false
                            }
                        );
                        $(document).on("click", "#accept-cookie", function(){
                            cookieToast.fadeOut();
                            let expire = new Date(new Date().setFullYear(new Date().getFullYear() + 1));
                            document.cookie = `accepted=true; expires=${expire}; path=' . SITE_ROOT . '/;`;
                        })
                    }
                )'
            );
        }
    }

    protected function addDefaultMetaTags(ControllerInterface $controller)
    {
        parent::addDefaultMetaTags($controller);
        $controller->addMetaTag("description", [
            "name" => "description",
            "content" => Variable::getByKey("meta_description")->value,
        ]);
        $controller->addMetaTag("keywords", [
            "name" => "keywords",
            "content" => Variable::getByKey("meta_keywords")->value
        ]);
    }
}
