<?php

namespace App\Controller;

use App\Form\LoginForm;
use App\Theme\CustomTheme;
use CoreDB\Kernel\ConfigurationManager;
use Src\Controller\LogoutController;
use Src\Entity\Translation;
use Src\Entity\User;
use Src\Views\Image;
use Src\Views\Navbar;
use Src\Views\NavItem;
use Src\Views\TextElement;

class LoginController extends CustomTheme
{
    public $form;
    public ?User $loginAsUser = null;
    public function __construct($arguments)
    {
        parent::__construct($arguments);
        $this->body_classes = ["bg-gradient-info"];
        $this->setTitle(Translation::getTranslation("welcome") . "!");
        if (isset($_GET["login_as_user"])) {
            $userClass = ConfigurationManager::getInstance()->getEntityInfo("users")["class"];
            $this->loginAsUser = $userClass::get($_GET["login_as_user"]);
        }
    }
    public function getTemplateFile(): string
    {
        return "page-login.twig";
    }

    public function checkAccess(): bool
    {
        return true;
    }

    public function buildNavbar()
    {
        $this->navbar = Navbar::create(
            "nav",
            "navbar navbar-expand navbar-light bg-white topbar mb-4 fixed-top shadow"
        );
        $currentUser = \CoreDB::currentUser();
        /**   */
        $userDropdown = NavItem::create(
            Image::create($currentUser->getProfilePhotoUrl(), $currentUser->getFullName(), false)
            ->addClass("img-profile rounded-circle"),
            ""
        );
        $userDropdown->addClass("ml-auto");
        if ($currentUser->isLoggedIn()) {
            $userDropdown->addDropdownItem(
                NavItem::create(
                    "fa fa-user",
                    $currentUser->getFullName(),
                    ProfileController::getUrl()
                )
            )->addDropdownItem(
                NavItem::create(
                    "fa fa-sign-out-alt",
                    Translation::getTranslation("logout"),
                    LogoutController::getUrl()
                )
            );
        } else {
            $userDropdown->addDropdownItem(
                NavItem::create(
                    "fa fa-sign-in-alt",
                    Translation::getTranslation("login"),
                    LoginController::getUrl()
                )
            );
        }
        $userDropdown->addDropdownItem(
            NavItem::create("", "", "")
            ->addClass("dropdown-divider")
        );
        $translateIcons = Translation::get(["key" => "language_icon"]);
        foreach (Translation::getAvailableLanguageList() as $language) {
            $userDropdown->addDropdownItem(
                NavItem::create(
                    TextElement::create($translateIcons->$language->getValue())
                    ->setTagName("div")
                    ->setIsRaw(true)
                    ->addClass("d-inline-block"),
                    Translation::getTranslation($language),
                    "?lang={$language}"
                )
            );
        }
        $this->navbar->addNavItem(
            $userDropdown
        );
    }

    public function preprocessPage()
    {
        if ($this->loginAsUser) {
            if ($this->loginAsUser->isAdmin()) {
                $this->createMessage(
                    Translation::getTranslation("cannot_login_as_another_admin_user")
                );
                \CoreDB::goTo(
                    @$_SERVER["HTTP_REFERER"] ?: BASE_URL
                );
            }
            $_SESSION[BASE_URL . "-BACKUP-UID"] = \CoreDB::currentUser()->ID;
            $_SESSION[BASE_URL . "-UID"] = $this->loginAsUser->ID;
            \CoreDB::goTo(BASE_URL);
        } else {
            if (\CoreDB::currentUser()->isLoggedIn()) {
                \CoreDB::goTo(BASE_URL);
            }
            $this->form = new LoginForm();
            $this->form->processForm();
        }
    }

    public function echoContent()
    {
        return $this->form;
    }
}
