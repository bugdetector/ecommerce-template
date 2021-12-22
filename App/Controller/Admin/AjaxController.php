<?php

namespace App\Controller\Admin;

use App\Controller\Admin\Orders\OpenbasketsController;
use App\Controller\PaymentController;
use App\Entity\Analytics\ProductTracker;
use App\Entity\Banner;
use App\Entity\Basket\Basket;
use App\Entity\Basket\BasketProduct;
use App\Entity\Branch;
use App\Entity\CustomUser;
use App\Entity\Product\ProductCategory;
use App\Entity\Product\ProductList;
use CoreDB;
use CoreDB\Kernel\Messenger;
use Exception;
use PDO;
use Src\Controller\Admin\AjaxController as AdminAjaxController;
use Src\Entity\DynamicModel;
use Src\Entity\Translation;
use Src\Form\SearchForm;
use Src\JWT;
use App\Lib\UserExportEntity;

class AjaxController extends AdminAjaxController
{
    public function checkAccess(): bool
    {
        return parent::checkAccess() || CoreDB::currentUser()->isUserInRole("Manager");
    }

    public function removeCategory()
    {
        $category = ProductCategory::get(@$_POST["nodeId"]);
        if ($category) {
            $category->delete();
            $this->createMessage(Translation::getTranslation("record_removed"), Messenger::SUCCESS);
        }
    }

    public function removeBanner()
    {
        $banner = Banner::get(@$_POST["nodeId"]);
        if ($banner) {
            $banner->delete();
            $this->createMessage(Translation::getTranslation("record_removed"), Messenger::SUCCESS);
        }
    }

    public function removeProductListItem()
    {
        $category = DynamicModel::get(@$_POST["nodeId"], ProductList::getTableName());
        if ($category) {
            $category->delete();
            $this->createMessage(Translation::getTranslation("record_removed"), Messenger::SUCCESS);
        }
    }

    public function removeBranch()
    {
        $branch = Branch::get(@$_POST["nodeId"]);
        if ($branch) {
            $branch->delete();
            $this->createMessage(Translation::getTranslation("record_removed"), Messenger::SUCCESS);
        }
    }

    public function isUserOrderExist()
    {
        $key = @$_POST["key"];
        try {
            $jwt = JWT::createFromString($key);
            $data = $jwt->getPayload();
            $referenceClass = \CoreDB::config()->getEntityInfo($data->entity)["class"];
            /** @var CustomUser */
            $user = $referenceClass::get($data->id);
            if (!$user || $referenceClass != CustomUser::class) {
                throw new Exception(
                    Translation::getTranslation("invalid_operation")
                );
            }
            $orderExist = Basket::get(["user" => $user->ID->getValue(), "is_ordered" => 1]) ? true : false;
            $translationKey = $orderExist ? "user_remove_accept_has_order" : "record_remove_accept_entity";
            return [
                "message" => Translation::getTranslation($translationKey, [
                    Translation::getTranslation("custom_users")
                ])
            ];
        } catch (Exception $ex) {
            throw new Exception($ex->getMessage());
        }
    }

    public function totalOrdersGraph()
    {
        switch (@$_GET["filter"]) {
            case "yearly":
                $extract = "YEAR";
                $dayFilter = "-10 year";
                $labelFilter = "Y";
                break;
            case "weekly":
                $extract = "WEEK";
                $dayFilter = "-1 year";
                $labelFilter = "F Y - W";
                break;
            case "daily":
                $extract = "DAY";
                $dayFilter = "-1 month";
                $labelFilter = "d F Y";
                break;
            case "hourly":
                $extract = "HOUR";
                $dayFilter = "-23 hour";
                $labelFilter = "H";
                break;
            // Default is monthly
            default:
                $extract = "YEAR_MONTH";
                $dayFilter = "-1 year";
                $labelFilter = "F Y";
                break;
        }
        $orders = \CoreDB::database()->select(Basket::getTableName())
        ->condition("is_ordered", 1)
        ->selectWithFunction([
            "EXTRACT($extract FROM order_time) AS month",
            "COUNT(*) AS count",
            "order_time"
        ])
        ->groupBy("month")
        ->condition("order_time", date("Y-m-d H:i:s", strtotime($dayFilter)), ">=")
        ->orderBy("order_time ASC")
        ->execute()->fetchAll(PDO::FETCH_OBJ);
        $filteredOrders = array_map(function ($el) {
            return $el->count;
        }, $orders);
        $labels = array_map(function ($el) use ($labelFilter) {
            return date($labelFilter, strtotime($el->order_time));
        }, $orders);
        $average = $filteredOrders ? round(array_sum($filteredOrders) / count($filteredOrders)) : 0;
        $response = [
            "type" => "line",
            "data" => [
                "labels" => $labels,
                "datasets" => [
                    [
                        "label" => Translation::getTranslation("total_orders"),
                        "backgroundColor" => "rgba(78, 115, 223)",
                        "borderColor" => "rgba(78, 115, 223)",
                        "fill" => false,
                        "data" => $filteredOrders,
                        "pointRadius" => 5
                    ],
                    [
                        "label" => Translation::getTranslation("average"),
                        "backgroundColor" => "rgba(204, 62, 52)",
                        "borderColor" => "rgb(204, 62, 52)",
                        "fill" => false,
                        "data" => array_fill(0, count($filteredOrders), $average),
                        "pointRadius" => 5,
                        "borderDash" => [5,1]
                    ]
                ]
            ],
            "options" => [
                "maintainAspectRatio" => false,
                "scales" => [
                    "xAxes" => [
                        [
                            "gridLines" => [
                                "display" => false
                            ]
                        ]
                    ]
                ],
                "elements" => [
                    "point" => [
                        "pointStyle" => "rectRounded"
                    ]
                ]
            ]
        ];
        return $response;
    }

    public function totalSalesGraph()
    {
        $orders = \CoreDB::database()->select(Basket::getTableName())
        ->condition("is_ordered", 1)
        ->selectWithFunction([
            "EXTRACT(YEAR_MONTH FROM order_time) AS month",
            "SUM(subtotal) AS sum",
            "order_time"
        ])
        ->groupBy("month")
        ->condition("order_time", date("Y-m-d H:i:s", strtotime("-1 year")), ">=")
        ->orderBy("month ASC")
        ->execute()->fetchAll(PDO::FETCH_OBJ);
        $monthlyOrders = array_map(function ($el) {
            return number_format($el->sum, 2, ".", "");
        }, $orders);
        $labels = array_map(function ($el) {
            return date("F Y", strtotime($el->order_time));
        }, $orders);
        $response = [
            "type" => "bar",
            "data" => [
                "labels" => $labels,
                "datasets" => [
                    [
                        "label" => Translation::getTranslation("total_sales"),
                        "backgroundColor" => "rgba(78, 115, 223)",
                        "borderColor" => "rgba(78, 115, 223)",
                        "fill" => false,
                        "data" => $monthlyOrders
                    ]
                ]
            ],
            "options" => [
                "maintainAspectRatio" => false,
                "scales" => [
                    "xAxes" => [
                        [
                            "gridLines" => [
                                "display" => false
                            ]
                        ]
                    ]
                ]
            ]
        ];
        return $response;
    }

    public function exportUsers()
    {
        $this->response_type = self::RESPONSE_TYPE_RAW;
        $form = SearchForm::createByObject(new UserExportEntity());
        $formData = $form->data;
        $headers = $form->headers;
        unset($headers["edit_actions"]);
        array_walk($formData, function (&$el) {
            unset($el["edit_actions"]);
        });
        header("Content-Type:application/csv");
        header(
            "Content-Disposition:attachment;filename="
            . Translation::getTranslation("users") . " - " . date("d-m-Y H:i:s") . ".csv"
        );
        $output = fopen("php://output", 'w');
        fputcsv($output, $headers);
        foreach ($formData as $row) {
            fputcsv($output, $row);
        }
    }

    public function productTrackerGraph()
    {
        $places = \CoreDB::database()->select(ProductTracker::getTableName(), "pt")
        ->join(BasketProduct::getTableName(), "bp", "pt.basket_product = bp.ID")
        ->join(Basket::getTableName(), "b", "bp.basket = b.ID")
        ->condition("b.is_ordered", 1)
        ->condition("pt.last_updated", date("Y-m-01"), ">=")
        ->condition("pt.place", "product_list", "!=")
        ->groupBy("pt.place")
        ->selectWithFunction([
            "pt.place",
            "COUNT(*) AS count"
        ])->execute()->fetchAll(\PDO::FETCH_OBJ);
        $placesData = array_map(function ($el) {
            return $el->count;
        }, $places);
        $labels = array_map(function ($el) {
            return Translation::getTranslation($el->place);
        }, $places);
        $response = [
            "type" => "bar",
            "data" => [
                "labels" => $labels,
                "datasets" => [
                    [
                        "label" => Translation::getTranslation("product_count"),
                        "backgroundColor" => "rgba(78, 115, 223)",
                        "borderColor" => "rgba(78, 115, 223)",
                        "fill" => false,
                        "data" => $placesData
                    ]
                ]
            ],
            "options" => [
                "maintainAspectRatio" => false,
                "scales" => [
                    "xAxes" => [
                        [
                            "gridLines" => [
                                "display" => false
                            ]
                        ]
                    ]
                ]
            ]
        ];
        return $response;
    }
    public function sendPaymentRequest()
    {
        $basket_id = $_GET["basket"];
        /** @var Basket */
        $basket = Basket::get($basket_id);
        /** @var CustomUser */
        $user = CustomUser::get($basket->user->getValue());
        CoreDB::HTMLMail(
            $user->email->getValue(),
            "Payment Resquest",
            Translation::getEmailTranslation("payment_request_mail", [
                $user->getFullName(),
                PaymentController::getUrl() . "?basket={$basket_id}",
                $basket->order_id->getValue()
            ], "en"),
            $user->getFullName()
        );
        \CoreDB::messenger()->createMessage(
            Translation::getTranslation("payment_request_mail_sent_successfully"),
            Messenger::SUCCESS
        );
        \CoreDB::goTo(
            @$_SERVER["HTTP_REFERER"] ?: OrdersController::getUrl()
        );
    }
    public function assignBasketToMe()
    {
        $basket_id = $_GET["basket_id"];
        $operation = @$_GET["operation"];
        /** @var Basket */
        $basket = Basket::get($basket_id);
        $basket->dealer->setValue(
            $operation == "unassign" ? null :
            CoreDB::currentUser()->ID->getValue()
        );
        $basket->save();
        \CoreDB::goTo(
            @$_SERVER["HTTP_REFERER"] ?: OpenbasketsController::getUrl()
        );
    }
    public function saveComment()
    {
        $user_id = $_POST["user_id"];
        $comment = $_POST["comment"];
        /** @var CustomUser */
        $user = CustomUser::get($user_id);
        $user->comment->setValue($comment);
        $user->comment_last_modified_by->setValue(
            CoreDB::currentUser()->ID->getValue()
        );
        $user->comment_last_modified_date->setValue(
            CoreDB::currentDate()
        );
        $user->save();
    }

    public function updateOrderStatus()
    {
        $basketId = $_POST["basket"];
        $status = $_POST["status"];
        /** @var Basket */
        $basket = Basket::get($basketId);
        $basket->status->setValue($status);
        $basket->save();
        \CoreDB::messenger()->createMessage(
            Translation::getTranslation("status_changed", [
                $basket->order_id->getValue(),
                Translation::getTranslation($status)
            ]),
            Messenger::SUCCESS
        );
    }
}
