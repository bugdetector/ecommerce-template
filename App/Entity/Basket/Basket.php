<?php

namespace App\Entity\Basket;

use App\Controller\Admin\Orders\InsertController;
use App\Entity\CustomUser;
use App\Entity\Postcode\Postcode;
use App\Entity\Product\Product;
use App\Entity\Product\ProductVariant;
use App\Entity\Product\Stock;
use App\Entity\UserAddress;
use App\Exception\BasketException;
use App\Form\OrderInsertForm;
use App\Views\BasketInvoice;
use CoreDB;
use CoreDB\Kernel\Database\DataType\Checkbox;
use CoreDB\Kernel\Model;
use CoreDB\Kernel\Database\DataType\TableReference;
use CoreDB\Kernel\Database\DataType\FloatNumber;
use CoreDB\Kernel\Database\DataType\Integer;
use CoreDB\Kernel\Database\DataType\ShortText;
use CoreDB\Kernel\Database\DataType\Text;
use CoreDB\Kernel\Database\DataType\DateTime;
use CoreDB\Kernel\Database\DataType\EnumaratedList;
use CoreDB\Kernel\Database\QueryCondition;
use CoreDB\Kernel\EntityReference;
use CoreDB\Kernel\Messenger;
use Exception;
use PDO;
use Src\Entity\DynamicModel;
use Src\Entity\Translation;
use Src\Entity\User;
use Src\Entity\Variable;
use Src\Views\Link;
use Src\Views\TextElement;

/**
 * Object relation with table basket
 * @author murat
 */

class Basket extends Model
{
    /**
     * TYPE_DELIVERY description.
     */
    public const TYPE_DELIVERY = "delivery";
    /**
     * TYPE_COLLECTION description.
     */
    public const TYPE_COLLECTION = "collection";
    /**
     * @var Integer $order_id
     * Order Id
     */
    public Integer $order_id;
    /**
     * @var TableReference $user
     *
     */
    public TableReference $user;
    /**
     * @var Integer $item_count
     *
     */
    public Integer $item_count;
    /**
     * @var FloatNumber $subtotal
     * Total price without taxes.
     */
    public FloatNumber $subtotal;
    /**
     * @var FloatNumber $total
     * Total price with taxes.
     */
    public FloatNumber $total;
    /**
     * @var FloatNumber $delivery
     *
     */
    public FloatNumber $delivery;
    /**
     * @var FloatNumber $vat
     * Vat tax price. (KDV tutarı.)
     */
    public FloatNumber $vat;
    /**
     * @var Checkbox $paid_online
     *
     */
    public Checkbox $paid_online;
    /**
     * @var Checkbox $is_ordered
     *
     */
    public Checkbox $is_ordered;
    /**
     * @var ShortText $ref
     *
     */
    public ShortText $ref;
    /**
     * @var Text $order_notes
     *
     */
    public Text $order_notes;
    /**
     * @var DateTime $order_time
     *
     */
    public DateTime $order_time;

    /**
     * @var ShortText $transaction_id
     *
     */
    public ShortText $transaction_id;
    /**
     * @var FloatNumber $paid_amount
     *
     */
    public FloatNumber $paid_amount;
    /**
     * @var Checkbox $private_products_excluded
     * Is private products excluded when checkout.
     */
    public Checkbox $private_products_excluded;

    public EntityReference $order_address;
    public EntityReference $billing_address;
    public EntityReference $order_item;

    /**
     * @var EnumaratedList $type
     * Ordered as delivery or collection.
     */
    public EnumaratedList $type;
    /**
     * @var TableReference $branch
     * Collection branch.
     */
    public TableReference $branch;
    /**
    * @var Checkbox $is_checked_out
    * Basket is checked out.
    */
    public Checkbox $is_checked_out;
    /**
    * @var DateTime $checkout_time
    *
    */
    public DateTime $checkout_time;
    /**
    * @var DateTime $delivery_date
    * Delivery date.
    */
    public DateTime $delivery_date;
    /**
    * @var Checkbox $stock_effected
    * Is stock effected on checkout.
    */
    public Checkbox $stock_effected;
    /**
    * @var Checkbox $is_canceled
    * Order is canceled.
    */
    public Checkbox $is_canceled;
    /**
    * @var DateTime $cancel_time
    * When order is canceled.
    */
    public DateTime $cancel_time;
    /**
    * @var TableReference $applied_voucher_code
    * Applied voucher code reference if exists.
    */
    public TableReference $applied_voucher_code;
    /**
    * @var FloatNumber $voucher_code_discount
    * Applied voucher code total discount.
    */
    public FloatNumber $voucher_code_discount;
    /**
     * @inheritdoc
     */
    /**
    * @var TableReference $dealer
    * Dealer user referance
    */
    public TableReference $dealer;
    public static function getTableName(): string
    {
        return "basket";
    }

    public static function getUserBasket(): Basket
    {
        $basket = Basket::get(["user" => \CoreDB::currentUser()->ID->getValue(), "is_ordered" => 0]);
        if (!$basket) {
            /** @var CustomUser */
            $currentUser = \CoreDB::currentUser();
            $basket = new Basket();
            $basket->user->setValue(\CoreDB::currentUser()->ID->getValue());
            $basket->total->setValue(0);
            $basket->is_ordered->setValue(0);
            $basket->type->setValue(
                $currentUser->shipping_option->getValue()
            );
            $basket->delivery_date->setValue(
                $currentUser->delivery_date->getValue()
            );
            switch ($basket->type->getValue()) {
                case Basket::TYPE_COLLECTION:
                    $basket->branch->setValue(
                        $currentUser->shipping_branch->getValue()
                    );
                    break;
            }
            if ($currentUser->isLoggedIn()) {
                $userAddress = UserAddress::get(
                    $currentUser->shipping_address->getValue(),
                    false
                );
                if ($userAddress) {
                    $basket->order_address->setValue(
                        [
                            $userAddress->toArray()
                        ]
                    );
                    $basket->billing_address->setValue(
                        $basket->order_address->getValue()
                    );
                    $basket->save();
                }
            }
        }
        return $basket;
    }

    /**
     * @return BasketProduct[]
     */
    public function getBasketProducts(): array
    {
        return BasketProduct::getAll(["basket" => $this->ID->getValue()]);
    }

    public function actions(): array
    {
        return [
            Link::create(
                InsertController::getUrl(),
                TextElement::create(
                    "<i class='fa fa-plus'></i> " . Translation::getTranslation("create_order")
                )->setIsRaw(true)
            )->addClass("btn btn-primary btn-sm")
        ];
    }

    public function getForm()
    {
        return new OrderInsertForm($this);
    }

    public function update()
    {
        $user = User::get($this->user->getValue());
        if (!$this->is_ordered->getValue()) {
            if (!$user->pay_optional_at_checkout->getValue()) {
                $this->paid_online->setValue(1);
            } else {
                $this->paid_online->setValue(0);
            }
        }
        if (isset($this->changed_fields["type"]) && $this->changed_fields["type"]) {
            parent::update();
            $basketProducts = $this->getBasketProducts();
            foreach ($basketProducts as $basketProduct) {
                $basketProduct->save();
            }
        }
        if (@$this->changed_fields["is_canceled"] && $this->is_canceled->getValue()) {
            $this->uncheckout();
        }
        $this->calculatePrices();
        $this->calculateItemCount();
        if (isset($this->changed_fields["is_ordered"]) && $this->is_ordered->getValue()) {
            $this->order_time->setValue(\CoreDB::currentDate());
            $this->order_id->setValue(
                $this->getNewOrderId()
            );
            if ($this->private_products_excluded->getValue()) {
                $orderItems = $this->order_item->getValue();
                $nonPrivateItems = [];
                $privateItems = [];
                foreach ($orderItems as $order_item) {
                    /** @var Product */
                    $product = Product::get($order_item["product"]);
                    if ($product->is_private_product->getValue()) {
                        $privateItems[] = $order_item;
                    } else {
                        $nonPrivateItems[] = $order_item;
                    }
                }
                if (empty($nonPrivateItems)) {
                    throw new Exception(
                        Translation::getTranslation("your_basket_empty")
                    );
                }
                $this->order_item->setValue($nonPrivateItems);
                $this->order_item->save();
                $result = parent::update();

                $basket = Basket::getUserBasket();
                $basket->order_item->setValue($privateItems);
                $basket->save();
            } else {
                $result = parent::update();
            }
            \CoreDB::messenger()->createMessage(
                Translation::getTranslation("order_success"),
                Messenger::INFO
            );
            /** @var CustomUser */
            $basketUser = CustomUser::get($this->user->getValue());
            $filename = "Order #{$this->order_id} - Cart Id: #{$this->ID}.pdf";
            /** @var OrderAddress */
            $address = OrderAddress::get(["order" => $this->ID->getValue()]);
            CoreDB::HTMLMail(
                $basketUser->email->getValue(),
                Translation::getTranslation("order_sent"),
                Translation::getEmailTranslation(
                    "order_sent",
                    [
                        $basketUser->getFullName(),
                        $this->order_id->getValue(),
                    ]
                ),
                $basketUser->getFullName(),
                [
                    [
                        "type" => "content",
                        "content" => $this->generatePdf(),
                        "filename" => $filename
                    ]
                ]
            );
            CoreDB::HTMLMail(
                Variable::getByKey("sales_report_mail")->value->getValue(),
                Translation::getTranslation("order_sent", null, "en") .
                " - " . $address->account_number->getValue(),
                Translation::getEmailTranslation(
                    "order_sent",
                    [
                    $basketUser->getFullName(),
                    $this->order_id->getValue(),
                    ],
                    "en"
                ),
                Variable::getByKey("site_name")->value->getValue(),
                [
                    [
                        "type" => "content",
                        "content" => $this->generatePdf(),
                        "filename" => $filename
                    ]
                ]
            );
            $this->changed_fields = [];
            return $result;
        }
        if (!$this->is_ordered->getValue()) {
            $this->order_id->setValue(null);
        }
        return parent::update();
    }

    public function checkout()
    {
        if (!$this->is_checked_out->getValue()) {
            $this->lockStockTable();
            $this->is_checked_out->setValue(1);
            $this->checkout_time->setValue(\CoreDB::currentDate());
            if (
                $this->type->getValue() != Basket::TYPE_COLLECTION ||
                strtotime($this->delivery_date->getValue()) <= strtotime("tomorrow 00:00:00")
            ) {
                $this->stock_effected->setValue(1);
                foreach ($this->order_item->getValue() as &$itemInfo) {
                    // Imex Hornsey default
                    $branch = $this->type->getValue() == Basket::TYPE_COLLECTION ?
                    $this->branch->getValue() : 1;
                    $productQuantity = \CoreDB::database()
                    ->select(Stock::getTableName())
                    ->select(Stock::getTableName(), ["quantity"])
                    ->condition("product", $itemInfo["product"])
                    ->condition("branch", $branch)
                    ->execute()->fetchObject()->quantity;
                    if ($productQuantity < $itemInfo["quantity"]) {
                        $this->unlockStockTable();
                        $product = Product::get($itemInfo["product"]);
                        throw new BasketException(BasketException::INSUFFICENT_QUANTITY, $product);
                    }
                    \CoreDB::database()->update(
                        Stock::getTableName(),
                        [
                            "quantity" => $productQuantity - $itemInfo["quantity"]
                        ]
                    )->condition("product", $itemInfo["product"])
                    ->condition("branch", $branch)
                    ->execute();
                }
            }
            $this->unlockStockTable();
            return true;
        }
    }

    public function uncheckout()
    {
        if ($this->is_checked_out->getValue()) {
            $this->lockStockTable();
            $this->is_checked_out->setValue(0);
            $this->checkout_time->setValue(null);
            if ($this->stock_effected->getValue()) {
                $this->stock_effected->setValue(0);
                foreach ($this->order_item->getValue() as &$itemInfo) {
                    $branch = $this->type->getValue() == Basket::TYPE_COLLECTION ?
                    $this->branch->getValue() : 1;
                    $productQuantity = \CoreDB::database()
                    ->select(Stock::getTableName())
                    ->select(Stock::getTableName(), ["quantity"])
                    ->condition("product", $itemInfo["product"])
                    ->condition("branch", $branch)
                    ->execute()->fetchObject()->quantity;
                    \CoreDB::database()->update(
                        Stock::getTableName(),
                        [
                            "quantity" => $productQuantity + $itemInfo["quantity"]
                        ]
                    )->condition("product", $itemInfo["product"])
                    ->condition("branch", $branch)
                    ->execute();
                }
            }
            $this->unlockStockTable();
            $this->save();
            return true;
        }
    }

    private function lockStockTable()
    {
        $stockTable = Stock::getTableName();
        \CoreDB::database()->query(
            "LOCK TABLES $stockTable WRITE"
        );
    }

    private function unlockStockTable()
    {
        \CoreDB::database()->query(
            "UNLOCK TABLES"
        );
    }

    private function getNewOrderId()
    {
        return \CoreDB::database()->select(Basket::getTableName())
            ->selectWithFunction(["MAX(order_id) AS new_order_id"])
            ->execute()->fetchObject()->new_order_id + 1;
    }

    public function addItem(Product $product, int $quantity = null, int $variation = null): BasketProduct
    {
        /** @var BasketProduct */
        $basketProduct = $this->getBasketProduct($product, $variation);

        if (
            $product->maximum_order_count->getValue() &&
            $quantity > $product->maximum_order_count->getValue()
        ) {
            throw new BasketException(
                BasketException::TYPE_MAXIMUM_ORDER,
                $product
            );
        }
        if ($quantity === null) {
            $quantity = $basketProduct->quantity->getValue();
            $quantity += 1;
        }
        if (!$basketProduct->ID->getValue() && $product->minimum_order_count->getValue() > 0) {
            $quantity = min(
                $quantity,
                $product->minimum_order_count->getValue()
            );
        }
        if ($quantity === 0) {
            $basketProduct->delete();
            $basketProduct = new BasketProduct();
        } elseif (
            $product->getQuantityInStock() >= $quantity ||
            (
                $product->exclude_stock->getValue() &&
                $this->type->getValue() == Basket::TYPE_COLLECTION &&
                strtotime($this->delivery_date->getValue()) > strtotime("tomorrow 00:00:00")
            )
        ) {
            if (
                $quantity < min(
                    $product->minimum_order_count->getValue(),
                    $product->getQuantityInStock()
                )
            ) {
                throw new BasketException(
                    BasketException::TYPE_MINIMUM_ORDER,
                    $product
                );
            }
            $basketProduct->quantity->setValue($quantity);
        } else {
            if ($quantity > $product->getQuantityInStock()) {
                $basketProduct->quantity->setValue($product->getQuantityInStock());
            }
            throw new BasketException(
                BasketException::TYPE_STOCK_EXCEED,
                $product
            );
        }
        if ($quantity) {
            $basketProduct->save();
        }
        $this->update();
        return $basketProduct;
    }

    public function calculatePrices()
    {
        if ($this->is_canceled->getValue()) {
            $this->vat->setValue(0);
            $this->delivery->setValue(0);
            $this->subtotal->setValue(0);
            $this->total->setValue(0);
            return;
        }
        $user = CustomUser::get($this->user->getValue());
        $subtotalQuery = CoreDB::database()->select(BasketProduct::getTableName(), "bp")
            ->condition("bp.basket", $this->ID)
            ->selectWithFunction(["SUM(bp.total_price) as total"]);
        if ($this->private_products_excluded->getValue()) {
            $subtotalQuery->join(Product::getTableName(), "p", "p.ID = bp.product")
                ->condition("p.is_private_product", 0);
        }
        $subtotal = $subtotalQuery->execute()->fetchObject()->total;
        $voucherCodeDiscount = 0;
        if ($this->applied_voucher_code->getValue()) {
            /** @var VoucherCode */
            $code = VoucherCode::get($this->applied_voucher_code->getValue());
            if ($code->type->getValue() == VoucherCode::TYPE_PERCENTAGE) {
                $voucherCodeDiscount = $subtotal * $code->discount_percentage->getValue() / 100;
            } elseif ($code->type->getValue() == VoucherCode::TYPE_EXACT_DISCOUNT) {
                $voucherCodeDiscount = $code->exact_discount->getValue();
            }
        }
        $vat = $this->calculateVat();
        $deliveryCalculationTotal = $subtotal + $vat;
        $minimumOrderPrice = $this->getMinimumOrderPrice();
        if ($this->type->getValue() == Basket::TYPE_COLLECTION || $deliveryCalculationTotal >= $minimumOrderPrice) {
            $delivery = 0;
        } else {
            $address = $this->order_address->getValue();
            /** @var Postcode */
            $postcode = $address ? Postcode::get([
                "postcode" => @explode(" ", $address[0]["postalcode"])[0]
            ]) : null;
            if ($postcode) {
                $delivery = $postcode->delivery->getValue();
            } else {
                $country = DynamicModel::get([
                    "ID" => @$user->address->getValue()[0]["country"]
                ], "countries") ?:  DynamicModel::get([
                    "code" => Variable::getByKey("default_country_code")->value->getValue()
                ], "countries");
                $delivery = $country->delivery_price->getValue();
            }
        }
        $vatTotal = $vat + (
            $delivery * self::getVatPercentage() / 100
        );
        $this->vat->setValue($vatTotal);
        $this->voucher_code_discount->setValue($voucherCodeDiscount);
        $this->delivery->setValue($delivery);
        $this->subtotal->setValue($subtotal);
        $this->total->setValue(round($subtotal + $delivery + $vatTotal - $voucherCodeDiscount, 2));
    }

    public function getMinimumOrderPrice()
    {
        switch ($this->type->getValue()) {
            case self::TYPE_COLLECTION:
                return 0;
            case self::TYPE_DELIVERY:
                $address = $this->order_address->getValue();
                /** @var Postcode */
                $postcode = $address ? Postcode::get([
                    "postcode" => @explode(" ", $address[0]["postalcode"])[0]
                ]) : null;
                if ($postcode) {
                    return $postcode->minimum_order_price->getValue();
                } else {
                    return Variable::getByKey("minimum_order_price")->value->getValue();
                }
        }
    }

    private function calculateItemCount()
    {
        $item_count = CoreDB::database()->select(BasketProduct::getTableName(), "bp")
            ->condition("bp.basket", $this->ID)
            ->selectWithFunction(["SUM(bp.quantity) as count"])
            ->execute()->fetchObject()->count;
        $this->item_count->setValue($item_count);
    }

    public function getBasketProduct(Product $product, int $variation = null): BasketProduct
    {
        $filter = [
            "basket" => $this->ID->getValue(),
            "product" => $product->ID->getValue(),
            "variant" => $variation
        ];
        /** @var BasketProduct */
        $basketProduct = BasketProduct::get($filter);
        if (!$basketProduct) {
            $basketProduct = new BasketProduct();
            $basketProduct->map($filter);
            $basketProduct->quantity->setValue(0);
            $basketProduct->variant->setValue($variation);
        }
        return $basketProduct;
    }

    public static function getVatPercentage(): float
    {
        $address = current(\CoreDB::currentUser()->address->getValue());
        if ($address) {
            $country = DynamicModel::get([
                "ID" => $address["country"]
            ], "countries");
            if ($country) {
                return $country->tax_percentage->getValue();
            }
        }
        return DynamicModel::get([
            "code" => Variable::getByKey("default_country_code")->value->getValue()
        ], "countries")->tax_percentage->getValue();
    }

    public function calculateVat(): float
    {
        return CoreDB::database()->select(BasketProduct::getTableName(), "bp")
            ->condition("bp.basket", $this->ID)
            ->selectWithFunction(["SUM(bp.item_vat) as total_vat"])
            ->execute()->fetchObject()->total_vat ?: 0;
    }

    public static function getMinimumPrivateItemCount()
    {
        return Variable::getByKey("minimum_private_order_count")->value->getValue();
    }

    public function getFormFields($name, bool $translateLabel = true): array
    {
        $fields = parent::getFormFields($name, $translateLabel);
        unset(
            $fields["is_ordered"],
        );
        return $fields;
    }

    public function editUrl($value = null)
    {
        if (!$value) {
            $value = $this->ID->getValue();
        }
        return InsertController::getUrl() . "{$value}";
    }

    public function generatePdf($outputMode = "S")
    {
        $basketInvoice = new BasketInvoice($this);
        $html2pdf = $basketInvoice->renderAsPdf();
        return $html2pdf->output("#{$this->order_id}-#{$this->ID}.pdf", $outputMode);
    }

    public function getSundriesTotalSatisfyMinimumPrice(): bool
    {
        $query = \CoreDB::database()->select(Basket::getTableName(), "b")
            ->join(BasketProduct::getTableName(), "bp", "bp.basket = b.ID")
            ->join(Product::getTableName(), "p", "bp.product = p.ID")
            ->condition("b.ID", $this->ID->getValue())
            ->condition("p.is_private_product", 0)
            ->selectWithFunction([
                "SUM(bp.total_price)"
            ]);
        $total = $query->execute()->fetchColumn();
        if (!$this->private_products_excluded->getValue() && $this->subtotal->getValue() > $total) {
            return !$total || $total > $this->getMinimumOrderPrice();
        }
        return true;
    }

    /**
     * @return Product[]
     */
    public function checkProductsNotSatisfys(): array
    {
        $productsNotSatisfy = [];
        if (!$this->private_products_excluded->getValue()) {
            $query = \CoreDB::database()->select(Basket::getTableName(), "b")
                ->join(BasketProduct::getTableName(), "bp", "bp.basket = b.ID")
                ->join(Product::getTableName(), "pr", "pr.ID = bp.product")
                ->leftjoin(ProductVariant::getTableName(), "pv", "pv.product = pr.ID OR pv.variant = pr.ID")
                ->condition("b.ID", $this->ID->getValue())
                ->condition("pr.is_private_product", 1)
                ->select("bp", ["product as bpp"])
                ->select("pv", ["product as prp"])
                ->groupBy("bp.product")
                ->having("pv.product IS NULL OR pv.product = bp.product");
            $variablesCondition = new QueryCondition($query);
            $variablesCondition->condition("pr.is_variable", 1, "=", "OR")
                ->condition("pr.is_private_product", 1, "OR");
            $query->condition($variablesCondition);
            $baseProducts = $query->execute()->fetchAll(PDO::FETCH_COLUMN);
            $minimumVariantCount = Basket::getMinimumPrivateItemCount();
            foreach ($baseProducts as $baseProduct) {
                $variantIds = [$baseProduct];
                foreach (ProductVariant::getAll(["product" => $baseProduct]) as $variant) {
                    $variantIds[] = $variant->variant->getValue();
                }
                $variantCount = \CoreDB::database()->select(Basket::getTableName(), "basket")
                    ->join(BasketProduct::getTableName(), "bp", "bp.basket = basket.ID")
                    ->join(Product::getTableName(), "p", "p.ID = bp.product")
                    ->condition("basket.ID", $this->ID->getValue())
                    ->condition("bp.product", $variantIds, "IN")
                    ->selectWithFunction([
                        "SUM(bp.quantity) as quantity"
                    ])->execute()->fetchObject()->quantity;
                /** @var Product */
                $product = Product::get($baseProduct);
                if ($variantCount <  ($product->minimum_order_count->getValue() ?: $minimumVariantCount)) {
                    $productsNotSatisfy[] = $product;
                }
            }
        }
        return $productsNotSatisfy;
    }

    /**
     * @return array [$disabledDays, $availableDays]
     */
    public function getDeliveryDayInfo(): array
    {
        $exploded = explode(" ", @$this->order_address->getValue()[0]["postalcode"]);
        /** @var Postcode */
        $postcode = $exploded ? $this->getPostcodeEntry($exploded[0]) : null;
        $disabledDays = [];
        if ($postcode) {
            $disabledDays = array_values(
                array_diff(range(0, 6), $postcode->day->getValue())
            );
        } else {
            $disabledDays = range(0, 6);
        }
        return [$disabledDays, $postcode ? $postcode->day->getValue() : []];
    }

    private function getPostcodeEntry(string $postcode): ?Postcode
    {
        $entry = Postcode::get([
            "postcode" => $postcode
        ]);
        $tmpPostcode = substr($postcode, 0, -1);
        while (!$entry && $tmpPostcode && strlen($tmpPostcode) >= 3) {
            $entry = Postcode::get([
                "postcode" => $tmpPostcode
            ]);
            $tmpPostcode = substr($tmpPostcode, 0, -1);
        }
        return $entry;
    }

    public function isDeliveryDayIsValid($delivery_date, $type = null): bool
    {
        if (
            strtotime($delivery_date) > strtotime("+2 weeks") ||
            strtotime($delivery_date) < strtotime("today 00:00")
        ) {
            return false;
        }
        if (($type ?: $this->type->getValue()) == Basket::TYPE_DELIVERY) {
            [$disabledDays, $availableDays] = $this->getDeliveryDayInfo();
            $datesAvailable = [];
            $timeStart = strtotime(date("H") < 18 ? "tomorrow" : "+2 day");
            for ($week = 0; $week <= 2; $week++) {
                foreach ($availableDays as $available) {
                    if ($available == 0) {
                        $available = 7;
                    } else {
                        $available--;
                    }
                    $date = date("Y-m-d", strtotime("this week +$available day +$week week"));
                    $time = strtotime($date);
                    if ($timeStart <= $time && $time <= strtotime("+2 weeks")) {
                        $datesAvailable[] = $date;
                    }
                }
            }
            return in_array(date("Y-m-d", strtotime($delivery_date)), $datesAvailable);
        }

        return true;
    }

    /**
     * @return Basket[]
     */
    public function getActiveOrders(): array
    {
        $activeOrdersQuery = \CoreDB::database()
        ->select(Basket::getTableName(), "b")
        ->condition("b.ID", $this->ID->getValue(), "!=")
        ->condition("b.user", $this->user->getValue())
        ->condition("b.is_canceled", 0)
        ->condition("b.type", Basket::TYPE_DELIVERY)
        ->condition(
            "b.delivery_date",
            date("Y-m-d 00:00:00", strtotime(
                date("H") >= 18 ? "+2 day" : "+1 day"
            )),
            ">="
        )
        ->select("b", ["ID"])
        ->orderBy("b.order_id DESC");
        $activeOrderIds = $activeOrdersQuery
        ->execute()->fetchAll(\PDO::FETCH_COLUMN);
        return array_map(function ($orderId) {
            return Basket::get($orderId);
        }, $activeOrderIds);
    }

    public function mergeWith(Basket $userBasket)
    {
        foreach ($userBasket->getBasketProducts() as $basketProduct) {
            /** @var BasketProduct */
            $existing = BasketProduct::get([
                "product" => $basketProduct->product->getValue(),
                "basket" => $this->ID->getValue(),
                "variant" => $basketProduct->variant->getValue() ?: null
            ]);
            $this->addItem(
                Product::get($basketProduct->product->getValue()),
                $basketProduct->quantity->getValue() + (
                    $existing ? $existing->quantity->getValue() : 0
                ),
                $basketProduct->variant->getValue() ?: null
            );
        }
    }

    public function __toString()
    {
        $basketSummary = [
            "#{$this->order_id}",
            (
                $this->type->getValue() == Basket::TYPE_COLLECTION ?
                "<i class='fa fa-walking'></i>" :
                "<i class='fa fa-truck'></i>"
            ) . " " .
            Translation::getTranslation($this->type->getValue()),
        ];
        $basketSummary[] = Translation::getTranslation("delivery_address") . ": " .
        OrderAddress::get(["order" => $this->ID->getValue()]);
        
        $basketSummary[] = Translation::getTranslation("date") . ": " .
        date("d-m-Y H:i", strtotime($this->delivery_date->getValue()));

        return implode("<br>", $basketSummary);
    }
}
