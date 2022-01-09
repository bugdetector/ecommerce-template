<?php

namespace App\Controller;

use App\Controller\Checkout\EmptybasketController;
use App\Controller\Checkout\OrdeviewController;
use App\Controller\Checkout\SentController;
use App\Entity\Basket\Basket;
use App\Entity\Basket\BasketProduct;
use App\Entity\Product\Product;
use App\Exception\BasketException;
use App\Form\CheckoutForm;
use App\Theme\CustomTheme;
use App\Views\BasketProductCard;
use App\Views\ProductList\CheckoutRecommendList;
use CoreDB\Kernel\Messenger;
use CoreDB\Kernel\Router;
use Src\Controller\NotFoundController;
use Src\Entity\Translation;
use Src\Form\Form;
use Src\Views\AlertMessage;
use Src\Views\CollapsableCard;
use Src\Views\Link;
use Src\Views\TextElement;
use Src\Views\ViewGroup;

class CheckoutController extends CustomTheme
{

    public ?Basket $basket;
    public ?CollapsableCard $privateProducts = null;
    public ViewGroup $basketProductCards;
    public ?Form $form;
    public ?CheckoutRecommendList $recommendList;
    public float $vatPercentage;
    public float $minimumOrder;
    protected bool $cardsEditable = true;

    public function __construct(array $arguments)
    {
        parent::__construct($arguments);
        $this->basket = $this->getBasket();
    }

    public function preprocessPage()
    {
        if (!$this->basket) {
            Router::getInstance()->route(NotFoundController::getUrl());
        } elseif ($this->basket->item_count->getValue() == 0 && !($this instanceof OrdeviewController)) {
            Router::getInstance()->route(EmptybasketController::getUrl());
        }
        $this->form = $this->getForm();
        if ($this->form) {
            $this->form->processForm();
        }
        $this->setTitle(Translation::getTranslation("checkout"));
        $this->vatPercentage = Basket::getVatPercentage();
        if (!$this->basket) {
            Router::getInstance()->route(NotFoundController::getUrl());
        }
        $this->minimumOrder = $this->basket->getMinimumOrderPrice();
        if (
            !in_array(
                get_class($this),
                [SentController::class, OrdeviewController::class]
            )
        ) {
            if ($activeOrders = $this->basket->getActiveOrders()) {
                if (count($activeOrders) == 1) {
                    /** @var Basket */
                    $activeOrder = $activeOrders[0];
                    $this->createMessage(
                        TextElement::create(
                            Translation::getTranslation("add_to_active_order_warning_one_item", [
                                date("d-m-Y", strtotime($activeOrder->delivery_date->getValue()))
                            ])
                        )->setIsRaw(true),
                        Messenger::WARNING
                    );
                } else {
                    $this->createMessage(
                        TextElement::create(
                            Translation::getTranslation("add_to_active_order_warning", [
                                count($activeOrders)
                            ])
                        )->setIsRaw(true),
                        Messenger::WARNING
                    );
                }
            }
            $deliveryCalculationPrice = $this->basket->subtotal->getValue() +
            $this->basket->calculateVat();
            if (
                $this->basket->type->getValue() != Basket::TYPE_COLLECTION &&
                $deliveryCalculationPrice < $this->minimumOrder &&
                $deliveryCalculationPrice > 0
            ) {
                $this->createMessage(
                    TextElement::create(
                        Translation::getTranslation("spend_another_warning", [
                            number_format(
                                $this->minimumOrder - $deliveryCalculationPrice,
                                2
                            )
                        ])
                    )->setIsRaw(true),
                    Messenger::INFO
                );
            }
            $this->recommendList = new CheckoutRecommendList();
        }
        $this->basketProductCards = ViewGroup::create("div", "");

        $privateProducts = [];
        $privateProductCount = 0;
        foreach ($this->basket->getBasketProducts() as $basketProduct) {
            $basketProductCard = BasketProductCard::create($basketProduct, $this->cardsEditable);
            if (!$basketProductCard->product) {
                continue;
            }
            if ($basketProductCard->product->is_private_product->getValue()) {
                $privateProducts[] = $basketProductCard;
                $privateProductCount += $basketProduct->quantity->getValue();
            } else {
                $this->basketProductCards->addField(
                    $basketProductCard
                );
            }
        }
        $this->checkStock();
        if (!empty($privateProducts)) {
            if ($this->basket->private_products_excluded->getValue()) {
                $buttonText = Translation::getTranslation("include_private_products");
                $opened = false;
            } else {
                $buttonText = Translation::getTranslation("exclude_private_products");
                $opened = true;
            }
            if (get_class($this) == CheckoutController::class) {
                $bespokeToggleButton =
                "<button class='btn btn-sm btn-primary float-end toggle-excluded'>{$buttonText}</button>";
            } else {
                $bespokeToggleButton = "";
            }
            $this->privateProducts = CollapsableCard::create(
                TextElement::create(
                    Translation::getTranslation("private_products") . $bespokeToggleButton
                )->setIsRaw(true)
            )->setId("private_products")
            ->setOpened($opened);
            $content = ViewGroup::create("div", "");
            $content->addField(
                AlertMessage::create(
                    Translation::getTranslation("bespoke_warning"),
                    AlertMessage::MESSAGE_TYPE_INFO
                )
            );
            $notSatisfiedProducts = $this->basket->checkProductsNotSatisfys();
            if (!empty($notSatisfiedProducts) && get_class($this) == CheckoutController::class) {
                foreach ($notSatisfiedProducts as $product) {
                    $content->addField(
                        AlertMessage::create(
                            Translation::getTranslation(
                                "minimum_private_error",
                                [
                                    $product->minimum_order_count->getValue() ?: Basket::getMinimumPrivateItemCount(),
                                    $product->title->getValue(),
                                ]
                            ),
                            AlertMessage::MESSAGE_TYPE_WARNING
                        )
                    );
                }
            }
            foreach ($privateProducts as $card) {
                $content->addField($card);
            }
            $this->privateProducts->setContent($content);
        }
        $this->addFrontendTranslation("empty_basket_confirm");
    }

    protected function checkStock()
    {
        if (!$this->basket->is_checked_out->getValue()) {
            /**
             * Checking stock available, minimum quantity, maximum quantity
             * @var BasketProduct
             */
            foreach ($this->basket->getBasketProducts() as $basketProduct) {
                $product = Product::get($basketProduct->product->getValue());
                try {
                    $this->basket->addItem(
                        $product,
                        $basketProduct->quantity->getValue(),
                        $basketProduct->variant->getValue() ?: null
                    );
                } catch (BasketException $ex) {
                    $this->createMessage(
                        $ex->getMessage(),
                        Messenger::WARNING
                    );
                    $this->basket->addItem(
                        $product,
                        $ex->getQuantity(),
                        $basketProduct->variant->getValue() ?: null
                    );
                }
            }
        }
    }

    protected function getBasket(): ?Basket
    {
        return Basket::getUserBasket();
    }

    protected function getForm(): ?Form
    {
        return new CheckoutForm();
    }

    public function getTemplateFile(): string
    {
        return "page-checkout.twig";
    }

    public function echoContent()
    {
        if ($this->privateProducts) {
            $content =  ViewGroup::create("div", "")
            ->addField($this->privateProducts);
            if (count($this->basketProductCards->fields)) {
                $content->addField(CollapsableCard::create(
                    Translation::getTranslation("sundries")
                )->setContent(
                    $this->basketProductCards
                )->setId("sundries")
                    ->setOpened(true));
            }
            if (get_class($this) == CheckoutController::class) {
                $content->addField(
                    Link::create(
                        "#",
                        TextElement::create(
                            "<i class='fa fa-trash'></i> " . Translation::getTranslation("empty_basket")
                        )->setIsRaw(true)
                    )->addClass("btn btn-danger mb-5 empty-basket float-end me-2")
                );
            }
            return $content;
            ;
        } else {
            if (get_class($this) == CheckoutController::class) {
                $this->basketProductCards->addField(
                    Link::create(
                        "#",
                        TextElement::create(
                            "<i class='fa fa-trash'></i> " . Translation::getTranslation("empty_basket")
                        )->setIsRaw(true)
                    )->addClass("btn btn-danger mb-5 empty-basket float-end me-2")
                );
            }
            return $this->basketProductCards;
        }
    }
}
