<?php

namespace App\Form;

use App\Entity\Product\Product;
use SimpleXLSX;
use Src\Entity\Translation;
use Src\Form\Form;
use Src\Form\Widget\InputWidget;
use Src\Views\TextElement;

class ProductsImportForm extends Form
{
    public string $method = "POST";
    public const REQUIRED_COLUMNS = [
        'Stockcode',
        'List Price',
    ];

    public $productsData = [];

    public function __construct()
    {
        parent::__construct();
        $this->setEnctype("multipart/form-data");
        $this->addField(
            InputWidget::create("import_file")
            ->setType("file")
            ->setLabel(
                Translation::getTranslation("upload_import_file")
            )->setDescription(
                TextElement::create(Translation::getTranslation("upload_file_description"))
                ->setIsRaw(true)
            )->addClass("h-100")
        );
        $this->addField(
            InputWidget::create("import")
            ->setType("submit")
            ->setValue(
                Translation::getTranslation("import")
            )->removeClass("form-control")
            ->addClass("btn btn-primary")
        );
    }

    public function getFormId(): string
    {
        return "products_import_form";
    }

    public function validate(): bool
    {
        if (!isset($_FILES["import_file"])) {
            return false;
        }
        $filePath = $_FILES["import_file"]["tmp_name"];
        $xlsx = SimpleXLSX::parse($filePath);
        $rows = $xlsx->rows();
        $headers = array_shift($rows);
        if (!empty(array_diff(self::REQUIRED_COLUMNS, $headers))) {
            $this->setError("import_file", Translation::getTranslation("file_columns_missing"));
        } else {
            foreach ($rows as $row) {
                $row = array_combine($headers, $row);
                foreach (self::REQUIRED_COLUMNS as $column) {
                    if ($row[$column] === '') {
                        $this->setError("import_file", Translation::getTranslation("file_data_missing"));
                        break 2;
                    }
                }
                $data = [
                    "stockcode" => $row["Stockcode"],
                    "price" => [
                        [
                            "item_count" => 0,
                            "price" => $row["List Price"]
                        ]
                    ]
                ];
                if (@$row["Title"]) {
                    $data["title"] = $row["Title"];
                } elseif (!Product::getByStockcode($row["Stockcode"])) {
                    $this->setError("import_file", Translation::getTranslation("title_missing", [$row["Stockcode"]]));
                }
                foreach (self::REQUIRED_COLUMNS as $column) {
                    unset($row[$column]);
                }
                if (isset($row["Bespoke"]) && $row["Bespoke"]) {
                    $data["is_private_product"] = 1;
                }
                if (isset($row["Special"]) && $row["Special"]) {
                    $data["is_special_product"] = 1;
                }
                if (isset($row["Variable"]) && $row["Variable"]) {
                    $data["is_variable"] = 1;
                }
                if (isset($row["Published"]) && $row["Published"]) {
                    $data["published"] = 1;
                }
                if (isset($row["Minimum Order"]) && $row["Minimum Order"]) {
                    $data["minimum_order_count"] = $row["Minimum Order"];
                }
                foreach ($row as $itemCount => $price) {
                    if (!$price || !is_numeric($itemCount)) {
                        continue;
                    }
                    $data["price"][] = [
                        "item_count" => $itemCount,
                        "price" => $price
                    ];
                }
                $this->productsData[] = $data;
            }
        }
        return true;
    }

    public function submit()
    {
        foreach ($this->productsData as $data) {
            $product = Product::getByStockcode($data["stockcode"]) ?: new Product();
            $product->map($data);
            try {
                $product->save();
            } catch (\Exception $ex) {
                $this->setError("", $ex->getMessage());
            }
        }
        $this->setMessage(
            Translation::getTranslation("count_item_imported", [count($this->productsData)])
        );
    }
}
