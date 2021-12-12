<?php

namespace App\Controller;

use App\AdminTheme\EcommerceAdminTheme;
use App\Controller\Admin\AjaxController;
use App\Controller\Admin\Orders\OpenbasketsController;
use App\Controller\Admin\OrdersController;
use App\Controller\Admin\Products\EnquirementController;
use App\Controller\Admin\Products\StockController;
use App\Controller\Admin\UsersController;
use App\Entity\Basket\Basket;
use App\Entity\CustomUser;
use App\Entity\Product\Enquirement;
use App\Entity\Search\SearchApi;
use App\Form\StockImportForm;
use App\Views\GraphView;
use App\Views\TotalOrdersGraphFilter;
use CoreDB;
use Src\Entity\Translation;
use Src\Entity\User;
use Src\Entity\Variable;
use Src\Views\BasicCard;
use Src\Views\CollapsableCard;
use Src\Views\Table;
use Src\Views\ViewGroup;

class AdminController extends EcommerceAdminTheme
{

    public function preprocessPage()
    {
        $this->setTitle(Variable::getByKey("site_name")->value . " | " . Translation::getTranslation("dashboard"));
        $this->number_of_members = CoreDB::database()->select(User::getTableName())
        ->selectWithFunction(["COUNT(*) as count"])
        ->execute()->fetchObject()->count;


        $waitingEnquirements = CoreDB::database()->select(Enquirement::getTableName(), "e")
        ->selectWithFunction(["COUNT(*) AS count"])
        ->condition("status", Enquirement::STATUS_OPEN)
        ->execute()->fetchColumn();

        $today = date("Y-m-d");
        $todaysOrders = \CoreDB::database()->select(Basket::getTableName(), "b")
        ->condition("is_ordered", 1)
        ->selectWithFunction(["COUNT(*) AS count"])
        ->condition("order_time", $today, ">=")
        ->execute()->fetchColumn();

        $thisMonth = date("Y-m-01");
        $thisMonthsOrders =  \CoreDB::database()->select(Basket::getTableName(), "b")
        ->condition("is_ordered", 1)
        ->selectWithFunction(["COUNT(*) AS count"])
        ->condition("order_time", $thisMonth, ">=")
        ->execute()->fetchColumn();

        $lastMonth = date("Y-m-01", strtotime("-1 month"));
        $lastMonthsOrders =  \CoreDB::database()->select(Basket::getTableName(), "b")
        ->condition("is_ordered", 1)
        ->selectWithFunction(["COUNT(*) AS count"])
        ->condition("order_time", $thisMonth, "<")
        ->condition("order_time", $lastMonth, ">=")
        ->execute()->fetchColumn();

        $openBaskets = CoreDB::database()->select(Basket::getTableName(), "b")
        ->condition("b.is_ordered", 0)
        ->condition("b.subtotal", 0, ">")
        ->selectWithFunction(["COUNT(*) AS count"])
        ->execute()->fetchColumn();

        $this->cards[] = BasicCard::create()
        ->setBorderClass("border-left-primary")
        ->setHref(UsersController::getUrl())
        ->setTitle(Translation::getTranslation("number_of_members"))
        ->setDescription($this->number_of_members)
        ->setIconClass("fa-user")
        ->addClass("col-lg-3 col-md-6 mb-4");

        $this->cards[] = BasicCard::create()
        ->setBorderClass("border-left-info")
        ->setHref(EnquirementController::getUrl() . "?status=open")
        ->setTitle(Translation::getTranslation("waiting_enquirys"))
        ->setDescription($waitingEnquirements)
        ->setIconClass("fa-exclamation")
        ->addClass("col-lg-3 col-md-6 mb-4");

        $this->cards[] = BasicCard::create()
        ->setBorderClass("border-left-warning")
        ->setHref(OpenbasketsController::getUrl())
        ->setTitle(Translation::getTranslation("open_baskets"))
        ->setDescription($openBaskets)
        ->setIconClass("fa-shopping-basket")
        ->addClass("col-lg-3 col-md-6 mb-4");

        $this->cards[] = BasicCard::create()
        ->setBorderClass("border-left-primary")
        ->setHref(OrdersController::getUrl() . "?order_time={$today} %26 {$today}")
        ->setTitle(Translation::getTranslation("today_orders"))
        ->setDescription($todaysOrders)
        ->setIconClass("fa-pound-sign")
        ->addClass("col-lg-3 col-md-6 mb-4");

        $this->cards[] = BasicCard::create()
        ->setBorderClass("border-left-success")
        ->setHref(OrdersController::getUrl() . "?order_time={$thisMonth} %26 {$today}")
        ->setTitle(Translation::getTranslation("this_month_orders"))
        ->setDescription($thisMonthsOrders)
        ->setIconClass("fa-pound-sign")
        ->addClass("col-lg-3 col-md-6 mb-4");

        $this->cards[] = BasicCard::create()
        ->setBorderClass("border-left-success")
        ->setHref(
            OrdersController::getUrl()
            . "?order_time={$lastMonth} %26 " . date("Y-m-d", strtotime($thisMonth . " -1 day"))
        )
        ->setTitle(Translation::getTranslation("last_month_orders"))
        ->setDescription($lastMonthsOrders)
        ->setIconClass("fa-pound-sign")
        ->addClass("col-lg-3 col-md-6 mb-4");

        $stockLastUpdated = Variable::getByKey(StockImportForm::STOCK_LAST_UPDATE_KEY);
        if ($stockLastUpdated) {
            $this->cards[] = BasicCard::create()
            ->setBorderClass("border-left-warning border-bottom-warning")
            ->setHref(StockController::getUrl())
            ->setTitle(Translation::getTranslation("stock_last_updated"))
            ->setDescription(date(
                "d-m-Y H:i:s",
                strtotime($stockLastUpdated->value->getValue())
            ))
            ->setIconClass("fa-clock")
            ->addClass("col-lg-3 col-md-6 mb-4");
        }

        $this->cards[] = CollapsableCard::create(
            Translation::getTranslation("total_orders")
        )
        ->setContent(
            ViewGroup::create("div", "")
            ->addField(
                TotalOrdersGraphFilter::create("div", "text-right")
            )
            ->addField(
                GraphView::create("div", "")
                ->setDataServiceUrl(AjaxController::getUrl() . "totalOrdersGraph")
                ->addAttribute("style", "height: 50vh")
            )
        )
        ->setOpened(true)
        ->setId("total_orders")
        ->addClass("col-12");
        $this->cards[] = CollapsableCard::create(
            Translation::getTranslation("total_sales")
        )
        ->setContent(
            GraphView::create("div", "")
            ->setDataServiceUrl(AjaxController::getUrl() . "totalSalesGraph")
            ->addAttribute("style", "height: 50vh")
        )
        ->setOpened(true)
        ->setId("total_sales")
        ->addClass("col-12");

        /** @var CustomUser */
        $user = new CustomUser();
        $usersTableHeaders = $user->getResultHeaders();
        $usersTableHeaders["edit_actions"] = "";
        $usersTableData = $user->getResultQuery()
        ->limit(20)
        ->execute()->fetchAll(\PDO::FETCH_ASSOC);
        foreach ($usersTableData as &$row) {
            $user->postProcessRow($row);
        }
        $this->cards[] = CollapsableCard::create(
            Translation::getTranslation("last_users_joined")
        )
        ->setContent(
            ViewGroup::create("div", "")
            ->addField(
                (new Table())->setHeaders($usersTableHeaders)
                ->setData($usersTableData)
            )
        )
        ->setOpened(true)
        ->setId("last_users_joined")
        ->addClass("col-12");

        $searchApi = new SearchApi();
        $searchApiTableHeaders = [
            Translation::getTranslation("word"),
            Translation::getTranslation("count")
        ];
        $searchApiTableData = \CoreDB::database()->select(SearchApi::getTableName(), "sa")
        ->select("sa", ["word", "search_count"])
        ->limit(50)
        ->orderBy("search_count DESC")
        ->execute()->fetchAll(\PDO::FETCH_ASSOC);
        foreach ($searchApiTableData as &$row) {
            $searchApi->postProcessRow($row);
        }
        $this->cards[] = CollapsableCard::create(
            Translation::getTranslation("most_searched_words")
        )
        ->setContent(
            ViewGroup::create("div", "")
            ->addField(
                (new Table())->setHeaders($searchApiTableHeaders)
                ->setData($searchApiTableData)
            )
        )
        ->setOpened(true)
        ->setId("most_searched_words")
        ->addClass("col-12");
    }
}
