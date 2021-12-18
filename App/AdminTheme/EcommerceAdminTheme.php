<?php

namespace App\AdminTheme;

use App\Controller\Admin\BannerController;
use App\Controller\Admin\BlogController;
use App\Controller\Admin\BoxController;
use App\Controller\Admin\BranchController;
use Src\Controller\AdminController;
use App\Controller\Admin\CategoryController;
use App\Controller\Admin\IssuesController;
use App\Controller\Admin\Orders\OpenbasketsController;
use App\Controller\Admin\OrdersController;
use App\Controller\Admin\ProductlistsController;
use App\Controller\Admin\Products\EnquirementController;
use App\Controller\Admin\UsersController;
use App\Controller\Admin\ProductsController;
use App\Controller\Admin\SpaceUnderBannerController;
use App\Controller\Admin\StatisticsController;
use App\Controller\Admin\VariationController;
use App\Controller\Admin\VoucherController;
use App\Controller\AdminController as ControllerAdminController;
use CoreDB;
use Src\Entity\Translation;
use Src\Views\NavItem;

class EcommerceAdminTheme extends AdminController
{
    public function checkAccess(): bool
    {
        return parent::checkAccess() || CoreDB::currentUser()->isUserInRole("Manager");
    }

    public static function getTemplateDirectories(): array
    {
        $directories = parent::getTemplateDirectories();
        array_unshift($directories, __DIR__ . "/templates");
        return $directories;
    }

    public function buildNavbar()
    {
        parent::buildNavbar();
        $this->navbar->addClass("fixed-top");
    }

    public function buildSidebar()
    {
        parent::buildSidebar();
        $currentUser = \CoreDB::currentUser();
        if (!$currentUser->isAdmin() && $currentUser->isUserInRole("Manager")) {
            $this->sidebar->addNavItem(
                NavItem::create(
                    "fa fa-tachometer-alt",
                    Translation::getTranslation("dashboard"),
                    BASE_URL . "/admin",
                    static::class == ControllerAdminController::class
                )
            );
        }
        if (
            $currentUser->isAdmin() ||
            $currentUser->isUserInRole("Manager") ||
            $currentUser->isUserInRole("Order Manager")
        ) {
            $this->sidebar
            ->addNavItem(
                new NavItem(
                    "fa fa-shopping-cart",
                    Translation::getTranslation("orders"),
                    OrdersController::getUrl(),
                    (
                    $this instanceof OrdersController &&
                    !($this instanceof OpenbasketsController)
                    )
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
        if ($currentUser->isAdmin() || $currentUser->isUserInRole("Manager")) {
            $this->sidebar->addNavItem(
                new NavItem(
                    "fa fa-users",
                    Translation::getTranslation("users"),
                    UsersController::getUrl(),
                    $this instanceof UsersController
                )
            )->addNavItem(
                NavItem::create(
                    "fa fa-tablets",
                    Translation::getTranslation("products"),
                    "#",
                    (
                    (
                        $this instanceof ProductsController &&
                        !($this instanceof EnquirementController)
                    ) ||
                    $this instanceof CategoryController ||
                    $this instanceof VariationController
                    )
                )->addCollapsedItem(
                    new NavItem(
                        "fa fa-tablets",
                        Translation::getTranslation("products"),
                        ProductsController::getUrl(),
                        $this instanceof ProductsController
                    )
                )->addCollapsedItem(
                    new NavItem(
                        "fa fa-stream",
                        Translation::getTranslation("categories"),
                        CategoryController::getUrl(),
                        $this instanceof CategoryController
                    )
                )->addCollapsedItem(
                    new NavItem(
                        "fa fa-list",
                        Translation::getTranslation("variation_option"),
                        VariationController::getUrl(),
                        $this instanceof VariationController
                    )
                )
            )->addNavItem(
                new NavItem(
                    "fa fa-chart-area",
                    Translation::getTranslation("statistics"),
                    StatisticsController::getUrl(),
                    $this instanceof StatisticsController
                )
            )->addNavItem(
                NavItem::create(
                    "fa fa-ad",
                    Translation::getTranslation("marketing"),
                    "#",
                    (
                    $this instanceof BranchController ||
                    $this instanceof ProductlistsController ||
                    $this instanceof BannerController ||
                    $this instanceof BlogController ||
                    $this instanceof SpaceUnderBannerController ||
                    $this instanceof BoxController
                    )
                )->addCollapsedItem(
                    new NavItem(
                        "fa fa-code-branch",
                        Translation::getTranslation("Branch"),
                        BranchController::getUrl(),
                        $this instanceof BranchController
                    )
                )->addCollapsedItem(
                    new NavItem(
                        "fa fa-list-alt",
                        Translation::getTranslation("product_lists"),
                        ProductlistsController::getUrl(),
                        $this instanceof ProductlistsController
                    )
                )->addCollapsedItem(
                    new NavItem(
                        "fa fa-images",
                        Translation::getTranslation("Banners"),
                        BannerController::getUrl(),
                        $this instanceof BannerController
                    )
                )->addCollapsedItem(
                    new NavItem(
                        "fa fa-blog",
                        Translation::getTranslation("blog"),
                        BlogController::getUrl(),
                        $this instanceof BlogController
                    )
                )->addCollapsedItem(
                    new NavItem(
                        "fa fa-boxes",
                        Translation::getTranslation("space_under_banner"),
                        AdminController::getUrl() . "space_under_banner",
                        $this instanceof SpaceUnderBannerController
                    )
                )->addCollapsedItem(
                    new NavItem(
                        "fa fa-boxes",
                        Translation::getTranslation("mainpage_box"),
                        BoxController::getUrl(),
                        $this instanceof BoxController
                    )
                )
            )->addNavItem(
                new NavItem(
                    "fa fa-qrcode",
                    Translation::getTranslation("voucher_codes"),
                    VoucherController::getUrl(),
                    $this instanceof VoucherController
                )
            )->addNavItem(
                NavItem::create(
                    "fa fa-tasks",
                    "FollowUp",
                    IssuesController::getUrl(),
                    static::class == IssuesController::class
                )
            )->addNavItem(
                new NavItem(
                    "fa fa-info",
                    Translation::getTranslation("enquiry"),
                    EnquirementController::getUrl(),
                    $this instanceof EnquirementController
                )
            );
        }
    }

    protected function addDefaultJsFiles()
    {
        parent::addDefaultJsFiles();
        $this->addJsFiles("dist/csl_global/csl_global.js");
    }

    protected function addDefaultCssFiles()
    {
        parent::addDefaultCssFiles();
        $this->addCssFiles("dist/csl_global/csl_global.css");
    }
}
