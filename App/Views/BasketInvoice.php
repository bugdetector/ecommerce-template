<?php

namespace App\Views;

use App\Entity\Basket\Basket;
use App\Entity\Basket\BasketProduct;
use App\Entity\Basket\BillingAddress;
use App\Entity\Basket\OrderAddress;
use App\Entity\Branch;
use App\Entity\AppUser;
use App\Entity\Product\Product;
use App\Entity\Product\VariationOption;
use App\Theme\AppTheme;
use Spipu\Html2Pdf\Html2Pdf;
use Src\Entity\Translation;
use Src\Entity\Variable;
use Src\Theme\CoreRenderer;
use Src\Theme\View;
use Src\Views\Table;
use Src\Views\TextElement;

class BasketInvoice extends View
{
    public Basket $basket;
    public AppUser $user;
    public Table $basketProductDataTable;

    public ?OrderAddress $orderAddress = null;
    public ?Branch $orderBranch = null;
    public ?BillingAddress $billingAddress = null;
    public function __construct(Basket $basket)
    {
        $this->basket = $basket;
        $this->user = AppUser::get($basket->user->getValue());

        $this->basketProductDataTable = new Table();
        $tableHeaders = [
            "stockcode",
            "title",
            "quantity",
            "item_per_price",
            "vat",
            "total"
        ];
        foreach ($tableHeaders as $index => $header) {
            $tableHeaders[$index] = Translation::getTranslation($header);
        }
        $tableContent = [];
        /** @var BasketProduct $basketProduct */
        $currencyIcon = Variable::getByKey("currency_icon")->value->getValue();
        foreach ($this->basket->getBasketProducts() as $basketProduct) {
            $variantId = $basketProduct->variant->getValue();
            $variantName = $variantId ? VariationOption::get($variantId)->title->getValue() : "";
            $product = Product::get($basketProduct->product->getValue()) ?: new Product();
            $tableContent[] = [
                $product->stockcode->getValue(),
                TextElement::create(
                    $product->title->getValue() . (
                        $variantName ? " - " . $variantName : ""
                    )
                )->addAttribute("style", "width: 270px")
                ->setTagName("div"),
                $basketProduct->quantity->getValue(),
                $currencyIcon . number_format($basketProduct->item_per_price->getValue(), 2, ".", ","),
                $currencyIcon . number_format($basketProduct->item_vat->getValue(), 2, ".", ","),
                $currencyIcon . number_format($basketProduct->total_price->getValue(), 2, ".", ",")
            ];
        }
        $this->basketProductDataTable->setHeaders($tableHeaders);
        $this->basketProductDataTable->setData($tableContent);

        $this->orderAddress = OrderAddress::get(["order" => $this->basket->ID->getValue()]);
        switch ($this->basket->type->getValue()) {
            case Basket::TYPE_COLLECTION:
                /** @var Branch */
                $this->orderBranch = Branch::get($this->basket->branch->getValue());
                break;
        }
        $this->billingAddress = BillingAddress::get([
            "order" => $this->basket->ID->getValue()
        ]);
    }

    public function getTemplateFile(): string
    {
        return "basket-invoice.twig";
    }

    public function renderAsPdf(): Html2Pdf
    {
        $html = CoreRenderer::getInstance(
            new AppTheme()
        )->renderView($this);
        $html2pdf = new Html2Pdf('P', 'A4', Translation::getLanguage(), true, "UTF-8");
        $html2pdf->setDefaultFont('dejavusans');
        $html2pdf->writeHTML($html);
        return $html2pdf;
    }
}
