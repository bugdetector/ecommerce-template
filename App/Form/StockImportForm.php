<?php

namespace App\Form;

use App\Entity\Product\Product;
use CoreDB\Kernel\Messenger;
use Src\Entity\Translation;
use Src\Entity\Variable;
use Src\Form\Form;
use Src\Form\Widget\InputWidget;
use Src\Views\TextElement;

class StockImportForm extends Form
{
    public const STOCK_LAST_UPDATE_KEY = "stock_last_updated";
    public string $method = "POST";

    public const HORNSEY = 1;
    public const LEYTON = 2;
    public const NEW_CROSS = 3;
    public const ACTON = 4;
    public const REQUIRED_COLUMNS = [
        "sku",
        "Code",
        "Avail (01)",
        "Avail (03)",
        "Avail (04)",
        "Avail (05)",
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
                TextElement::create(Translation::getTranslation("stock_upload_file_description"))
                ->setIsRaw(true)
            )->addClass("h-100")
            ->addAttribute("required", "true")
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
        if (!isset($_FILES["import_file"]) || !$_FILES["import_file"]) {
            return false;
        }
        $csvFile = @fopen($_FILES["import_file"]["tmp_name"], "r");
        if (!$csvFile) {
            return false;
        }
        $rows = [];
        while (($row = fgetcsv($csvFile)) !== false) {
            $rows[] = $row;
        }
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
                $product = Product::getByStockcode($row["sku"]);
                if (!$product) {
                    $this->setMessage(
                        Translation::getTranslation("stockcode_not_found", [
                            $row["sku"]
                        ]),
                        Messenger::INFO
                    );
                    continue;
                }
                $stock = [
                    [
                        "branch" => self::HORNSEY,
                        "quantity" => $row["Avail (01)"]
                    ],
                    [
                        "branch" => self::LEYTON,
                        "quantity" => $row["Avail (03)"]
                    ],
                    [
                        "branch" => self::NEW_CROSS,
                        "quantity" => $row["Avail (04)"]
                    ],
                    [
                        "branch" => self::ACTON,
                        "quantity" => $row["Avail (05)"]
                    ],
                ];
                $this->productsData[$row["sku"]] = $stock;
            }
        }
        return true;
    }

    public function submit()
    {
        \CoreDB::database()->beginTransaction();
        foreach ($this->productsData as $stockcode => $stock) {
            $product = Product::getByStockcode($stockcode);
            $product->map([
                "stock" => $stock
            ]);
            try {
                $product->save();
            } catch (\Exception $ex) {
                $this->setError("", $ex->getMessage());
            }
        }
        $stockUpdateDate = Variable::getByKey(self::STOCK_LAST_UPDATE_KEY) ?:
        Variable::create(self::STOCK_LAST_UPDATE_KEY);
        $stockUpdateDate->map([
            "type" => "datetime",
            "value" => \CoreDB::currentDate()
        ]);
        $stockUpdateDate->save();
        \CoreDB::database()->commit();
        $this->setMessage(
            Translation::getTranslation("count_item_imported", [count($this->productsData)])
        );
    }
}
