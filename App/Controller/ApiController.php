<?php

namespace App\Controller;

use App\Entity\Analytics\ProductTracker;
use App\Entity\Basket\Basket;
use App\Entity\Product\Product;
use App\Entity\Search\SearchApi;
use App\Exception\BasketException;
use CoreDB;
use CoreDB\Kernel\Messenger;
use CoreDB\Kernel\Router;
use CoreDB\Kernel\ServiceController;
use Exception;
use Src\Controller\AccessdeniedController;
use Src\Entity\Translation;
use Src\Entity\Variable;

class ApiController extends ServiceController
{

    /**
     * @inheritdoc
     */
    public function checkAccess(): bool
    {
        return boolval($this->method);
    }

    public function search()
    {
        $search = $_GET["search"];
        return $search ? SearchApi::getSearchResult($search) : [];
    }

    public function addItemToBasket()
    {
        if (
            !CoreDB::currentUser()->isLoggedIn() &&
            !Variable::getByKey("non_login_order")->value->getValue()
        ) {
            Router::getInstance()->route(AccessdeniedController::getUrl());
        }
        $itemId = @$_POST["itemId"];
        $quantity = @$_POST["quantity"];
        $variation = @$_POST["variation"];
        $place = @$_POST["place"];
        /** @var Product */
        $product = Product::get($itemId);
        if (
            $product &&
            !$product->is_special_product->getValue() &&
            $product->isPrivateAndOwnerMatches() &&
            ( $product->is_variable->getValue() ? $variation : true )
        ) {
            $basket = Basket::getUserBasket();
            try {
                if ($basket->uncheckout()) {
                    $product = Product::get($itemId);
                }
                $basketProduct = $basket->addItem($product, $quantity, $variation ?: null);
                $response = $basketProduct->toArray();
                if ($place) {
                    $tracker = ProductTracker::get([
                        "basket_product" => $basketProduct->ID->getValue()
                    ]) ?: new ProductTracker();
                    $tracker->map([
                        "basket_product" => $basketProduct->ID->getValue(),
                        "place" => $place,
                        "url" => @$_SERVER["HTTP_REFERER"]
                    ]);
                    $tracker->save();
                }
            } catch (BasketException $ex) {
                http_response_code(400);
                $this->createMessage($ex->getMessage());
                return [
                    "quantity" => $ex->getQuantity()
                ];
            }
            $response += $basket->toArray();
            $response["for_free_delivery"] = $basket->getMinimumOrderPrice() -
            $basket->subtotal->getValue() -
            $basket->calculateVat();
            $this->createMessage(
                Translation::getTranslation("added_to_basket", [
                    $quantity,
                    $product->title
                ]),
                Messenger::SUCCESS
            );
            return $response;
        } elseif ($itemId == "update") {
            $basket = Basket::getUserBasket();
            return $basket->toArray();
        } else {
            throw new Exception(
                Translation::getTranslation("invalid_operation")
            );
        }
    }

    public function cleanBasket()
    {
        if (!Variable::getByKey("non_login_order")->value->getValue()) {
            Router::getInstance()->route(AccessdeniedController::getUrl());
        }
        $basket = Basket::getUserBasket();
        $basket->order_item->setValue([]);
        $basket->save();
        $this->createMessage(
            Translation::getTranslation("basket_cleaned"),
            Messenger::SUCCESS
        );
    }
}
