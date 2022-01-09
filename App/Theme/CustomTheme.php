<?php

namespace App\Theme;

use App\Controller\AccountController;
use App\Controller\Admin\Orders\OpenbasketsController;
use App\Controller\Admin\OrdersController;
use App\Controller\CheckoutController;
use App\Controller\FavoritesController;
use App\Controller\MyordersController;
use App\Controller\ProductsController;
use App\Controller\ProfileController;
use App\Controller\RegisterController;
use App\Controller\ShippingController;
use App\Controller\WelcomeController;
use App\Entity\Basket\Basket;
use App\Entity\Basket\BasketProduct;
use App\Entity\Branch;
use App\Entity\CustomUser;
use App\Entity\Product\Product;
use App\Entity\Product\VariationOption;
use App\Entity\UserAddress;
use App\Views\CategoryNavbar;
use CoreDB;
use CoreDB\Kernel\Messenger;
use PDO;
use Src\BaseTheme\BaseTheme;
use Src\Controller\LoginController;
use Src\Controller\LogoutController;
use Src\Entity\Translation;
use Src\Entity\Variable;
use Src\Views\Image;
use Src\Views\Navbar;
use Src\Views\NavItem;
use Src\Views\TextElement;
use Src\Views\ViewGroup;

class CustomTheme extends BaseTheme
{
    public $body_classes = ["csl-theme"];
    public ?Navbar $categoryNavbar;

    public function processPage()
    {
        $this->addDefaultMetaTags();
        $this->addDefaultJsFiles();
        $this->addDefaultCssFiles();
        $this->addDefaultTranslations();
        $this->buildNavbar();
        $this->buildCategoryNavbar();
        $this->buildSidebar();
        $this->preprocessPage();
        $this->render();
    }

    public function checkAccess(): bool
    {
        /** @var CustomUser */
        $user = \CoreDB::currentUser();
        if (!$user->email_verified->getValue()) {
            \CoreDB::goTo(WelcomeController::getUrl());
        }
        if (!$user->shipping_option->getValue()) {
            \CoreDB::goTo(ShippingController::getUrl());
        }
        $deliveryDate = date("Y-m-d", strtotime($user->delivery_date->getValue()));
        if (
            $user->shipping_option->getValue() == CustomUser::SHIPPING_OPTION_COLLECTION &&
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

    public static function getTemplateDirectories(): array
    {
        $directories = parent::getTemplateDirectories();
        array_unshift($directories, __DIR__ . "/templates");
        return $directories;
    }

    public function buildNavbar()
    {
        $this->navbar = Navbar::create(
            "nav",
            "navbar navbar-expand navbar-light bg-white topbar mb-4 fixed-top shadow"
        );
        $currentUser = \CoreDB::currentUser();
        if (!$currentUser->isAdmin() && !$currentUser->isUserInRole("Manager")) {
            $this->navbar->addClass("sidebar-hidden");
        }
        /** @var NavItem */
        $userDropdown = NavItem::create(
            Image::create($currentUser->getProfilePhotoUrl(), "", true)
                ->addClass("img-profile rounded-circle"),
            ""
        )->addClass("user-dropdown");

        $this->navbar->addField(
            TextElement::create(
                "<div class='input-group'>
                    <input type='text' id='navbar-search-input' class='form-control' 
                    value='" . htmlspecialchars(@$_GET["search"]) . "' autocomplete='off' 
                    name='search' placeholder='" . Translation::getTranslation("search") . "'/>
                    <a href='#' class='' id='search-clear-button'
                    onclick='event.preventDefault(); $(\"#navbar-search-input\").val(\"\");'
                    >
                        <i class='fa fa-times'></i>
                        <span class='sr-only'>" . Translation::getTranslation("clear") . "</span>
                    </a>
                    <div class='input-group-append'>
                        <button type='submit' class='btn btn-info'>
                            <i class='fa fa-search'></i>
                            <span class='sr-only'>" . Translation::getTranslation("search") . "</span>
                        </button>
                    </div>
                </div>"
            )->setIsRaw(true)
                ->setTagName("form")
                ->addAttribute("id", "navbar-search")
                ->addAttribute("action", ProductsController::getUrl())
        );
        $phone = Variable::getByKey("general_enquiries_phone");
        if ($phone->value->getValue()) {
            $callItem = NavItem::create(
                "fa fa-phone-alt me-1 ms-2",
                TextElement::create(
                    Translation::getTranslation("call")
                )->addClass("d-none d-md-block fw-bold"),
                "tel:{$phone->value}"
            );
            $callItem->fields[0]->addClass("text-primary");
    
            $this->navbar->addNavItem(
                $callItem
            );
        }
        if ($currentUser->isLoggedIn()) {
            $this->navbar->addNavItem($this->getBasketNav());

            $userDropdown->addDropdownItem(
                NavItem::create(
                    "fa fa-user",
                    Translation::getTranslation("account_settings"),
                    ProfileController::getUrl()
                )->addClass("ms-1")
            )->addDropdownItem(
                NavItem::create(
                    "fa fa-shopping-cart me-1",
                    Translation::getTranslation("my_orders"),
                    MyordersController::getUrl()
                )
            )->addDropdownItem(
                NavItem::create(
                    "fa fa-star me-1",
                    Translation::getTranslation("favorites"),
                    FavoritesController::getUrl()
                )
            )->addDropdownItem(
                NavItem::create(
                    "fa fa-sign-out-alt",
                    Translation::getTranslation("logout"),
                    LogoutController::getUrl()
                )->addClass("ms-1")
            )->addDropdownItem(
                NavItem::create("", "", "")
                ->addClass("dropdown-divider")
            )->addDropdownItem(
                NavItem::create(
                    "fa fa-envelope",
                    $currentUser->email->getValue(),
                    AccountController::getUrl()
                )->addClass("ms-1")
            );
            /** @var UserAddress */
            $shippingAddress = UserAddress::get(
                $currentUser->shipping_address->getValue(),
                false
            );
            if ($shippingAddress) {
                $userDropdown->addDropdownItem(
                    NavItem::create(
                        "fa fa-user",
                        $shippingAddress->account_number,
                        AccountController::getUrl()
                    )->addClass("ms-1")
                );
            }
        } else {
            $userDropdown
                ->addDropdownItem(
                    NavItem::create(
                        "fa fa-sign-in-alt",
                        Translation::getTranslation("login"),
                        LoginController::getUrl()
                    )
                )->addDropdownItem(
                    NavItem::create(
                        "fa fa-user-plus",
                        Translation::getTranslation("register"),
                        RegisterController::getUrl()
                    )
                )->addClass("ms-auto");
        }
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

    public function buildCategoryNavbar()
    {
        $this->categoryNavbar = CategoryNavbar::create(
            "nav",
            "navbar navbar-expand-lg navbar-dark bg-primary mb-4"
        );
        $currentUser = \CoreDB::currentUser();
            $icon = null;
            $descriptionNav = null;
            $shippingUrl = ShippingController::getUrl() . "?destination=" . CoreDB::requestUrl();
        switch ($currentUser->shipping_option->getValue()) {
            case CustomUser::SHIPPING_OPTION_COLLECTION:
                $icon = "fa fa-walking";
                /** @var Branch */
                $branch = Branch::get($currentUser->shipping_branch->getValue());
                if (!$branch) {
                    if (!$this instanceof ShippingController) {
                        \CoreDB::goTo(ShippingController::getUrl());
                    }
                }
                $descriptionNav = NavItem::create(
                    "fa fa-building",
                    @$branch->name,
                    $shippingUrl
                );
                break;
            case CustomUser::SHIPPING_OPTION_DELIVERY:
                $icon = "fa fa-truck";
                /** @var UserAddress */
                $shippingAddress = UserAddress::get(
                    $currentUser->shipping_address->getValue(),
                    false
                );
                $descriptionNav = NavItem::create(
                    "fa fa-map-marker-alt",
                    $shippingAddress,
                    $shippingUrl
                );
                break;
        }
        if ($icon) {
            $changeButton = TextElement::create(
                Translation::getTranslation("change_shipping_option") .
                    " <i class='fa fa-chevron-right'></i>"
            )->setIsRaw(true)
                ->setTagName("a")
                ->addAttribute("href", $shippingUrl)
                ->addClass("dropdown-item bg-info text-white");
            $shippingDropDown = NavItem::create(
                $icon,
                Translation::getTranslation($currentUser->shipping_option->getValue())
            )->addDropdownItem(
                $descriptionNav
                    ->addField(
                        $changeButton
                    )
            );
            $shippingDropDown->removeClass("no-arrow")
                ->addClass("d-lg-none");
            $shippingDropDown->fields[0]->addClass("text-white");
            $shippingDropDown->fields[1]->addAttribute(
                "style",
                "right: auto"
            );
            $this->categoryNavbar->addNavItem(
                $shippingDropDown
            );
    
            $desktopShippingDropdown = NavItem::create(
                $icon,
                TextElement::create(
                    Translation::getTranslation($currentUser->shipping_option->getValue())
                )->addClass("mx-1")
            )->addDropdownItem(
                $descriptionNav
            )->addClass(
                "d-none d-lg-block fw-bold"
            );
            $desktopShippingDropdown->removeClass("no-arrow");
            $desktopShippingDropdown->fields[0]->addClass("text-primary");
            $this->navbar->addNavItem(
                $desktopShippingDropdown
            );
        }
    }

    public function getBasketNav(): NavItem
    {
        $basket = Basket::getUserBasket();
        $basketProducts = $basket->getBasketProducts();

        $basketNav = NavItem::create(
            "fa fa-shopping-basket text-basket",
            ViewGroup::create("span", "badge bg-danger badge-counter shop-item-count")
                ->addField(
                    TextElement::create($basket->item_count->getValue())
                )
        );
        $basketNav->addClass("shopping-basket ms-auto");
        $basketNav->addDropdownItem(
            NavItem::create(
                "",
                TextElement::create(
                    Translation::getTranslation("total") .
                        ": " . Variable::getByKey("currency_icon")->value->getValue() .
                        "<span class='basket-subtotal'>" .
                        number_format($basket->subtotal->getValue(), 2, '.', '') .
                        "</span>"
                )->setTagName("b")
                    ->setIsRaw(true)
            )->addClass("checkout-section")->addField(
                TextElement::create(
                    Translation::getTranslation("checkout") . " <i class='fa fa-chevron-right'></i>"
                )->setIsRaw(true)
                    ->setTagName("a")
                    ->addAttribute("href", CheckoutController::getUrl())
                    ->addClass("dropdown-item bg-info text-white")
            )
        );

        $variantOptions = \CoreDB::database()->select(VariationOption::getTableName(), "vo")
            ->select("vo", ["ID", "title"])
            ->execute()->fetchAll(PDO::FETCH_KEY_PAIR);
        /** @var BasketProduct $basketItem */
        foreach ($basketProducts as $basketItem) {
            /** @var Product */
            $product = Product::get($basketItem->product->getValue());
            $imageUrl = $product->getCoverImageUrl();
            $variantId = $basketItem->variant->getValue();
            $productItem = NavItem::create(
                Image::create(
                    $imageUrl,
                    $product->title->getValue() ?: ""
                )->addClass("dropdown-list-image me-3"),
                ViewGroup::create("div", "")
                    ->addField(
                        TextElement::create(
                            $product->title->getValue() .
                                ($variantId ? " - " . $variantOptions[$variantId] : "")
                        )
                            ->addClass("fw-bold")
                    )->addField(
                        TextElement::create(
                            "<button type='button' class='btn btn-sm btn-danger drop-from-basket'
                            data-item='{$basketItem->product}' data-variant='{$basketItem->variant}'>
                                <i class='fa fa-trash'></i>
                            </button>
                            <div class='btn-group my-2'>
                                <button type='button' class='btn btn-sm btn-info quantity-down'
                                data-item='{$basketItem->product}' data-variant='{$basketItem->variant}'>
                                    <i class='fa fa-minus'></i>
                                </button>
                                <input type='number' class='btn btn-sm btn-primary quantity'
                                data-item='{$basketItem->product}' data-variant='{$basketItem->variant}'
                                value='{$basketItem->quantity}' readonly/>
                                <button type='button' class='btn btn-sm btn-info quantity-up'
                                data-item='{$basketItem->product}' data-variant='{$basketItem->variant}'>
                                    <i class='fa fa-plus'></i>
                                </button>
                            </div>"
                        )->setIsRaw(true)
                            ->setTagName("div")
                    )->addField(
                        TextElement::create(
                            Variable::getByKey("currency_icon")->value->getValue() .
                                number_format($basketItem->total_price->getValue(), 2, '.', '')
                        )
                            ->setTagName("div")
                            ->addClass("total-value fw-bold")
                            ->addAttribute("data-item", $basketItem->product)
                            ->addAttribute("data-variant", strval($basketItem->variant))
                    )
            )->addAttribute("data-item", $basketItem->product)
                ->addAttribute("data-variant", strval($basketItem->variant) ?: "null")
                ->addClass("basket-item");
            $productItem->fields[0]->addClass("d-flex align-items-center")->setTagName("div");
            $basketNav->addDropdownItem($productItem);
        }
        $basketNav->fields[1]->addClass("dropdown-list");
        return $basketNav;
    }

    public function buildSidebar()
    {
        $currentUser = \CoreDB::currentUser();
        if (
            $currentUser->isAdmin() ||
            $currentUser->isUserInRole("Manager") ||
            $currentUser->isUserInRole("Order Manager")
        ) {
            parent::buildSidebar();
            if (!$currentUser->isAdmin() && $currentUser->isUserInRole("Manager")) {
                $this->sidebar->addNavItem(
                    NavItem::create(
                        "fa fa-tachometer-alt",
                        Translation::getTranslation("dashboard"),
                        BASE_URL . "/admin",
                        static::class == AdminController::class
                    )
                );
            } elseif ($currentUser->isUserInRole("Order Manager")) {
                $this->sidebar->addNavItem(
                    new NavItem(
                        "fa fa-shopping-cart",
                        Translation::getTranslation("orders"),
                        OrdersController::getUrl(),
                        ($this instanceof OrdersController &&
                            !($this instanceof OpenbasketsController))
                    )
                )->addNavItem(
                    NavItem::create(
                        "fa fa-shopping-basket",
                        Translation::getTranslation("open_baskets"),
                        OpenbasketsController::getUrl(),
                        static::class == OpenbasketsController::class
                    )
                );
            }
        }
    }

    protected function addDefaultJsFiles()
    {
        parent::addDefaultJsFiles();
        $this->addJsFiles("dist/basket/basket.js");
        $this->addJsFiles("dist/csl_global/csl_global.js");
        $this->addJsFiles("dist/navbar-search/navbar-search.js");
        $tagManagerId = Variable::getByKey("google_tag_manager_id")->value->getValue();
        $this->addJsFiles("https://www.googletagmanager.com/gtag/js?id={$tagManagerId}");
        $this->addJsCode(
            "window.dataLayer = window.dataLayer || [];
            function gtag(){dataLayer.push(arguments);}
            gtag('js', new Date());
            gtag('config', '{$tagManagerId}');"
        );
    }

    protected function addDefaultCssFiles()
    {
        parent::addDefaultCssFiles();
        $this->addCssFiles("dist/basket/basket.css");
        $this->addCssFiles("dist/csl_global/csl_global.css");
        $this->addCssFiles("dist/navbar-search/navbar-search.css");
    }

    protected function addDefaultTranslations()
    {
        parent::addDefaultTranslations();
        $this->addFrontendTranslation("record_remove_accept");
        $this->addFrontendTranslation("add_to_basket");
        $this->addFrontendTranslation("please_enter_quantity");
        $this->addFrontendTranslation("added_to_basket");
        if (!@$_COOKIE["accepted"]) {
            $this->addFrontendTranslation("cookie_accept_message");
            $this->addFrontendTranslation("allow_cookies");
            $this->addJsCode(
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

    protected function addDefaultMetaTags()
    {
        parent::addDefaultMetaTags();
        $this->addMetaTag("description", [
            "name" => "description",
            "content" => Variable::getByKey("meta_description")->value,
        ]);
        $this->addMetaTag("keywords", [
            "name" => "keywords",
            "content" => Variable::getByKey("meta_keywords")->value
        ]);
    }
}
