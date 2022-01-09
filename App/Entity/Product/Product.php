<?php

namespace App\Entity\Product;

use App\Controller\Admin\Products\ImportController;
use App\Controller\Admin\Products\InsertController;
use App\Controller\Admin\Products\StockController;
use App\Controller\Admin\Products\StockimportController;
use App\Entity\Basket\Basket;
use App\Entity\Basket\BasketProduct;
use App\Entity\CustomUser;
use App\Entity\Search\SearchApi;
use CoreDB;
use CoreDB\Kernel\Database\DataType\Checkbox;
use CoreDB\Kernel\Database\DataType\Date;
use CoreDB\Kernel\Model;
use CoreDB\Kernel\Database\DataType\ShortText;
use CoreDB\Kernel\Database\DataType\LongText;
use CoreDB\Kernel\Database\DataType\File;
use CoreDB\Kernel\Database\DataType\FloatNumber;
use CoreDB\Kernel\Database\DataType\Integer;
use CoreDB\Kernel\Database\DataType\TableReference;
use CoreDB\Kernel\EntityReference;
use CoreDB\Kernel\XMLSitemapEntityInterface;
use CoreDB\Kernel\XMLSitemapUrl;
use Src\Entity\File as EntityFile;
use Src\Entity\Translation;
use Src\Entity\User;
use Src\Entity\Variable;
use Src\Entity\Watchdog;
use Src\Form\Widget\OptionWidget;
use Src\Form\Widget\SelectWidget;
use Src\Theme\View;
use Src\Views\Link;
use Src\Views\TextElement;
use Src\Views\ViewGroup;

/**
 * Object relation with table products
 * @author murat
 */

class Product extends Model implements XMLSitemapEntityInterface
{
    /**
    * @var ShortText $stockcode
    * User input stockcode.
    */
    public ShortText $stockcode;
    /**
    * @var ShortText $title
    * Title of product.
    */
    public ShortText $title;
    /**
    * @var LongText $description
    * Product description. Please use Description Attachments section to add new image or file in the description.
    */
    public LongText $description;

    public EntityReference $product_description_attachment;

    public EntityReference $product_info;

    /**
    * @var TableReference $category
    * Product category.
    */
    public TableReference $category;
    /**
    * @var File $image
    * Product image.
    */
    public File $image;
    public EntityReference $product_picture;
    /**
    * @var EntityReference $stock
    * Quantity in stock.
    */
    public EntityReference $stock;
    /**
    * @var FloatNumber $vat
    * VAT percentage
    */
    public FloatNumber $vat;
    /**
    * @var Checkbox $published
    * Is product published.
    */
    public Checkbox $published;
    /**
    * @var Checkbox $special_price_not_available
    * If checked special price won't applied.
    */
    public Checkbox $special_price_not_available;

     /**
    * @var Checkbox $is_special_product
    * Special products are not shown on catalogue.
    * They list on special products page.
    * Prices not shown "Enquire" button placed instead.
    */
    public Checkbox $is_special_product;

    /**
    * @var Checkbox $is_variable
    * If checked a selectbox shown under product card to select variation.
    */
    public Checkbox $is_variable;

    public EntityReference $product_variant;
    public EntityReference $price;

    /**
    * @var Checkbox $is_private_product
    * Is this product is private and has one or many owner.
    */
    public Checkbox $is_private_product;
    /**
    * @var Integer $minimum_order_count
    * Minimum order count that this item can be checkout.
    */
    public Integer $minimum_order_count;
    /**
    * @var Integer $maximum_order_count
    * Maximum order that this item can be ordered in a day.
    */
    public Integer $maximum_order_count;
    /**
    * @var Integer $weight
    * Weight used ordering.
    (Old system: sprice9 column)
    */
    public Integer $weight;
    /* @var Date $sprice_valid_from
    * Sprice is valid after this day. Including selected.
    */
    public Date $sprice_valid_from;
    /**
    * @var Date $sprice_valid_to
    * Sprice is valid before this day. Including selected.
    */
    public Date $sprice_valid_to;
    /**
    * @var Checkbox $exclude_stock
    * If checked this product stock quantity is not important for next day collection.
    */
    public Checkbox $exclude_stock;

    public EntityReference $private_product_owner;
    /**
    * @var ShortText $url_alias
    *
    */
    public ShortText $url_alias;

    /**
     * @inheritdoc
     */
    public static function getTableName(): string
    {
        return "products";
    }

    public static function getByStockcode(string $stockcode): ?Product
    {
        return self::get(["stockcode" => $stockcode]);
    }

    public static function getByUrlAlias($url_alias)
    {
        return static::get(["url_alias" => $url_alias]);
    }

    public function actions(): array
    {
        return [
            ViewGroup::create("a", "btn btn-sm btn-primary shadow-sm me-1 ms-auto mb-1")
                ->addField(
                    ViewGroup::create("i", "fa fa-plus text-white-50")
                )->addAttribute("href", InsertController::getUrl())
                ->addField(TextElement::create(Translation::getTranslation("add_new_product"))),
            Link::create(
                ImportController::getUrl(),
                TextElement::create(
                    "<i class='fa fa-file-import text-white-50'></i> " . Translation::getTranslation("import")
                )->setIsRaw(true)
            )->addClass("btn btn-sm btn-info shadow-sm me-1 mb-1"),
            Link::create(
                StockController::getUrl(),
                TextElement::create(
                    "<i class='fa fa-eye text-white-50'></i> " . Translation::getTranslation("show_stock")
                )->setIsRaw(true)
            )->addClass("btn btn-sm btn-info shadow-sm me-1 mb-1"),
            Link::create(
                StockimportController::getUrl(),
                TextElement::create(
                    "<i class='fa fa-file-import text-white-50'></i> " . Translation::getTranslation("update_stock")
                )->setIsRaw(true)
            )->addClass("btn btn-sm btn-info shadow-sm me-1 mb-1")
        ];
    }

    public function editUrl($value = null)
    {
        if (!$value) {
            $value = $this->ID->getValue();
        }
        return InsertController::getUrl() . "{$value}";
    }

    public function getCoverImageUrl()
    {
        if ($pictures = $this->product_picture->getValue()) {
            $info = current($pictures);
            /** @var EntityFile $file */
            $file = EntityFile::get($info["image"]);
            return $file->getUrl();
        } else {
            return BASE_URL . "/assets/awaiting-image.jpg";
        }
    }

    public function getFormFields($name, bool $translateLabel = true): array
    {
        $fields = parent::getFormFields($name, $translateLabel);
        $categories = ProductCategory::getRootElements();
        $options = $this->getCategoryOptions($categories);
        /** @var SelectWidget */
        $categoryInput = $fields["category"];
        $categoryInput->options = $options;
        $categoryInput->removeClass("selectpicker")
        ->removeClass("autocomplete")
        ->setValue("");
        $fields["category"] = $categoryInput;
        return $fields;
    }

    protected function getFieldWidget(string $field_name, bool $translateLabel): ?View
    {
        if ($field_name == "url_alias") {
            return null;
        } else {
            return parent::getFieldWidget($field_name, $translateLabel);
        }
    }

    private function getCategoryOptions(array $categories): array
    {
        $options = [];
        /** @var ProductCategory $category */
        foreach ($categories as $category) {
            if ($subNodes = $category->getSubNodes()) {
                $optionGroup = ViewGroup::create("optgroup", "")
                ->addAttribute("label", $category->name->getValue());
                $subCategories = $this->getCategoryOptions($subNodes);
                foreach ($subCategories as $option) {
                    $optionGroup->addField($option);
                }
                $options[] = $optionGroup;
            } else {
                $option = new OptionWidget($category->ID->getValue(), $category->name->getValue());
                if ($this->category->getValue() == $category->ID->getValue()) {
                    $option->setSelected(true);
                }
                $options[] = $option;
            }
        }
        return $options;
    }

    public function save()
    {
        if (
            @$this->changed_fields["published"] &&
            $this->changed_fields["published"]["new_value"] == 0
        ) {
            $basketsHasProduct = \CoreDB::database()
            ->select(Product::getTableName(), "p")
            ->join(BasketProduct::getTableName(), "bp", "bp.product = p.ID")
            ->join(Basket::getTableName(), "b", "b.ID = bp.basket AND b.is_ordered = 0")
            ->select("b", ["ID"])
            ->execute()->fetchAll(\PDO::FETCH_COLUMN);
            foreach ($basketsHasProduct as $basketId) {
                /** @var Basket */
                $basket = Basket::get($basketId);
                $basket->addItem($this, 0);
            }
        }
        $prices = $this->price->getValue() ?: [];
        foreach ($prices as $index => $price) {
            if (!$price["price"]) {
                unset($prices[$index]);
            }
        }
        usort($prices, function ($a, $b) {
            return $a["item_count"] > $b["item_count"];
        });
        $this->price->setValue($prices);
        if (@$this->changed_fields["title"]) {
            $this->url_alias->setValue($this->generateUrlAlias());
        }
        return parent::save();
    }

    public function delete(): bool
    {
        $productOrdered = \CoreDB::database()
        ->select(BasketProduct::getTableName(), "bp")
        ->condition("product", $this->ID->getValue())
        ->selectWithFunction(["COUNT(*)"])
        ->execute()->fetchColumn();
        if ($productOrdered) {
            throw new \Exception(
                Translation::getTranslation("product_ordered")
            );
        }
        \CoreDB::database()->delete(FavoriteProducts::getTableName())
        ->condition("product", $this->ID->getValue())
        ->execute();
        return parent::delete();
    }

    protected function insert()
    {
        $result = parent::insert();
        if ($result) {
            $filteredTitle = preg_replace(
                "/[^A-Za-z ]/",
                "",
                $this->title->getValue()
            );
            $titleParts = array_filter(explode(" ", $filteredTitle));
            $searchParts = [];
            foreach ($titleParts as $part) {
                if (strlen($part) > 3) {
                    $searchParts[] = $part;
                    SearchApi::set($part);
                }
            }
            try {
                SearchApi::set(implode(" ", $searchParts));
            } catch (\Exception $ex) {
                Watchdog::log("search_api_generate_product_insert", $ex->getMessage());
            }
        }
    }

    protected function generateUrlAlias()
    {
        $urlAlias = urlencode(
            preg_replace(
                "/[^a-z0-9_]/",
                "",
                mb_strtolower(
                    str_replace(" ", "_", $this->title)
                )
            )
        );
        $tempUrlAlias = $urlAlias;
        $tempCount = 1;
        $product = Product::getByUrlAlias($tempUrlAlias);
        while ($product && $product->ID->getValue() != $this->ID->getValue()) {
            $tempUrlAlias = $urlAlias . "-" . $tempCount;
            $tempCount++;
            $product = Product::getByUrlAlias($tempUrlAlias);
        }
        return $tempUrlAlias;
    }

    public function getPrices()
    {
        if (Variable::getByKey("collection_order_enabled")->value->getValue()) {
            $currentUser = \CoreDB::currentUser();
            if ($currentUser->isLoggedIn()) {
                $shippingOption = $currentUser->shipping_option->getValue();
                return [
                    $shippingOption => $this->getPrice($shippingOption)
                ];
            } else {
                return [
                    ProductPrice::PRICE_TYPE_DELIVERY => $this->getPrice(ProductPrice::PRICE_TYPE_DELIVERY),
                    ProductPrice::PRICE_TYPE_COLLECTION => $this->getPrice(ProductPrice::PRICE_TYPE_COLLECTION),
                ];
            }
        } else {
            return [
                "price" => $this->getPrice(null)
            ];
        }
    }

    private function getPrice($deliveryType)
    {
        $listPrice = 0;
        foreach ($this->price->getValue() as $priceInfo) {
            if ($priceInfo["price_type"] == $deliveryType) {
                $listPrice = $priceInfo["price"];
                break;
            }
        }
        return [
            "list_price" => floatval($listPrice),
            "offer" => $this->getPriceForQuantity(0, $deliveryType)
        ];
    }

    private function getPriceList($deliveryType = null, $userId = null)
    {
        if (!$this->price->getValue()) {
            return [];
        }
        if ($userId) {
            /** @var User $user */
            $user = CustomUser::get($userId);
        } else {
            $user = CoreDB::currentUser();
        }
        if (!$deliveryType && Variable::getByKey("collection_order_enabled")->value->getValue()) {
            $deliveryType = $user->isLoggedIn() ?
            ProductPrice::PRICE_TYPE_DELIVERY : $user->shipping_option->getValue();
        }
        $priceList = array_filter(
            $this->price->getValue(),
            function ($price) use ($deliveryType) {
                return $deliveryType  == $price["price_type"];
            }
        );
        if ($this->special_price_not_available->getValue()) {
            return $priceList;
        } else {
            $specialFilteredPriceList = array_filter($priceList, function ($el) use ($user) {
                return $el["item_count"] >= $user->special_price_available->getValue();
            });
            if (empty($specialFilteredPriceList)) {
                $specialFilteredPriceList = $priceList;
            }
            return $specialFilteredPriceList;
        }
    }

    public function getPriceForQuantity(int $quantity, $deliveryType = null, $userId = null): float
    {
        $productListEntry = $this->getProductListEntry();
        if ($productListEntry) {
            return $productListEntry->getPrice();
        } else {
            $prices = $this->getPriceList($deliveryType, $userId);
            $price = @current($prices)["price"];
            // If price is not valid, accept only list prices
            $today = date("Y-m-d");
            if (
                $this->sprice_valid_to->getValue() <  $today ||
                $this->sprice_valid_from->getValue() > $today
            ) {
                $prices = array_splice($prices, 0, 1);
            }
            foreach ($prices as $priceInfo) {
                if ($quantity >= $priceInfo["item_count"]) {
                    $price = $priceInfo["price"];
                }
            }
            return floatval($price);
        }
    }

    public function getQuantityInStock(): int
    {
        /** @var CustomUser */
        $user = \CoreDB::currentUser();
        // if not set use Imex Hornsey
        $branch = $user->shipping_option->getValue() == CustomUser::SHIPPING_OPTION_COLLECTION ?
        $user->shipping_branch->getValue() : 1;
        foreach ($this->stock->getValue() as $quantity) {
            if ($quantity["branch"] == $branch) {
                return $quantity["quantity"];
            }
        }
        // if there is no quantity entry return 0
        return 0;
    }

    public function getProductListEntry(): ?ProductList
    {
        return ProductList::getListEntry($this->ID->getValue());
    }

    public function isPrivateAndOwnerMatches(CustomUser $user = null): bool
    {
        if ($this->is_private_product->getValue()) {
            if (!$user) {
                $user = \CoreDB::currentUser();
            }
            foreach ($this->private_product_owner->getValue() as $owner) {
                if ($owner["owner"] == $user->ID->getValue()) {
                    return true;
                }
            }
            return false;
        }
        return true;
    }

    public function getBreadCrumb(): array
    {
        /** @var ProductCategory */
        $category = ProductCategory::get($this->category->getValue());
        $breadCrumb = [$category];
        while (
            $category &&
            $category->parent->getValue() &&
            $category = ProductCategory::get(["ID" => $category->parent->getValue()])
        ) {
            array_unshift($breadCrumb, $category);
        }
        return $breadCrumb;
    }
    public function getImages()
    {
        if (!empty($this->product_picture->getValue())) {
            $fileIds = [];
            foreach ($this->product_picture->getValue() as $pictureInfo) {
                $fileIds[] = $pictureInfo["image"];
            }
            return EntityFile::getAll([
                "ID" => $fileIds
            ]);
        }
    }

    public static function getXmlSitemapUrls(): array
    {
        return \CoreDB::database()->select(Product::getTableName(), "p")
        ->condition("published", 1)
        ->selectWithFunction([
            "CONCAT('" . BASE_URL . "', '/products/product/', p.url_alias) AS loc",
            "DATE_FORMAT(p.last_updated, '%Y-%m-%d') AS lastmod"
        ])->execute()->fetchAll(\PDO::FETCH_CLASS, XMLSitemapUrl::class);
    }
}
