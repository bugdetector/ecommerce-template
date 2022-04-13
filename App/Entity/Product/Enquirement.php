<?php

namespace App\Entity\Product;

use App\Controller\Admin\Products\Enquirement\InsertController;
use App\Entity\UserAddress;
use CoreDB\Kernel\Model;
use CoreDB\Kernel\Database\DataType\TableReference;
use CoreDB\Kernel\Database\DataType\Integer;
use CoreDB\Kernel\Database\DataType\LongText;
use CoreDB\Kernel\Database\DataType\EnumaratedList;
use CoreDB\Kernel\Database\SelectQueryPreparerAbstract;
use Src\Entity\Translation;
use Src\Entity\User;
use Src\Entity\Variable;
use Src\Views\Link;
use Src\Views\TextElement;

/**
 * Object relation with table enquirements
 * @author murat
 */

class Enquirement extends Model
{
    /**
    * STATUS_OPEN description.
    */
    public const STATUS_OPEN = "open";
    /**
    * STATUS_CLOSED description.
    */
    public const STATUS_CLOSED = "closed";

    /**
    * @var TableReference $product
    * Enquired product.
    */
    public TableReference $product;
    /**
    * @var TableReference $user
    * Enquired user.
    */
    public TableReference $user;
    /**
    * @var Integer $quantity
    * Enquired quantity.
    */
    public Integer $quantity;
    /**
    * @var LongText $description
    * Customer enquire description.
    */
    public LongText $description;
    /**
    * @var EnumaratedList $status
    * Enquirement status.
    */
    public EnumaratedList $status;

    /**
     * @inheritdoc
     */
    public static function getTableName(): string
    {
        return "enquirements";
    }

    public static function getUserActiveEnquirement($itemId): ?Enquirement
    {
        return Enquirement::get([
            "user" => \CoreDB::currentUser()->ID->getValue(),
            "product" => $itemId,
            "status" => Enquirement::STATUS_OPEN
        ]);
    }

    public static function getMinimumEnquirementCount()
    {
        return Variable::getByKey("minimum_enquirement_count")->value->getValue();
    }

    public function getSearchFormFields(bool $translateLabel = true): array
    {
        $formFields = parent::getSearchFormFields($translateLabel);
        unset(
            $formFields["ID"],
            $formFields["description"],
        );
        return $formFields;
    }

    public function getResultHeaders(bool $translateLabel = true): array
    {
        $headers = parent::getResultHeaders($translateLabel);
        unset(
            $headers["ID"],
            $headers["description"]
        );
        return $headers;
    }

    public function getResultQuery(): SelectQueryPreparerAbstract
    {
        return \CoreDB::database()->select(Enquirement::getTableName(), "e")
        ->select("e", ["ID as edit_actions"])
        ->join(Product::getTableName(), "p", "p.ID = e.product")
        ->select("p", ["stockcode"])
        ->join(User::getTableName(), "u", "e.user = u.ID")
        ->select("e", ["quantity", "status", "created_at", "last_updated"]);
    }

    public function postProcessRow(&$row): void
    {
        parent::postProcessRow($row);
        $row["status"] = Translation::getTranslation($row["status"]);
        $row["created_at"] = date("d-m-Y H:i", strtotime($row["created_at"]));
        $row["last_updated"] = date("d-m-Y H:i", strtotime($row["last_updated"]));
    }

    public function editUrl($value = null)
    {
        return InsertController::getUrl() . $value;
    }

    public function actions(): array
    {
        return [
            Link::create(
                InsertController::getUrl(),
                TextElement::create(
                    "<i class='fa fa-plus text-light-50'></i> " . Translation::getTranslation("add")
                )->setIsRaw(true)
            )->addClass("btn btn-sm btn-primary shadow-sm me-1 mb-1")
        ];
    }
}
