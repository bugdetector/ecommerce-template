<?php

namespace App\Entity;

use App\Controller\Admin\AjaxController;
use App\Controller\Admin\Users\ChangeController;
use App\Controller\Admin\Users\InsertController;
use App\Controller\VerifyController;
use App\Entity\Basket\Basket;
use App\Entity\Product\FavoriteProducts;
use App\Entity\Product\PrivateProductOwner;
use App\Form\UserInsertForm;
use CoreDB\Kernel\Database\DataType\Checkbox;
use CoreDB\Kernel\Database\DataType\DateTime;
use CoreDB\Kernel\Database\DataType\EnumaratedList;
use CoreDB\Kernel\Database\DataType\File;
use CoreDB\Kernel\Database\DataType\Integer;
use CoreDB\Kernel\Database\DataType\ShortText;
use CoreDB\Kernel\Database\DataType\TableReference;
use CoreDB\Kernel\Database\DataType\Text;
use CoreDB\Kernel\Database\SelectQueryPreparerAbstract;
use CoreDB\Kernel\EntityReference;
use Src\Entity\Role;
use Src\Entity\Translation;
use Src\Entity\User;
use Src\Entity\Variable;
use Src\Form\Widget\CollapsableWidgetGroup;
use Src\Theme\View;
use Src\Views\Link;
use Src\Views\TextElement;
use Src\Views\ViewGroup;

/**
 * Object relation with table custom_users
 * @author murat
 */

class AppUser extends User
{
    /**
    * List producst on grid.
    */
    public const PRODUCT_CARD_LIST_OPTION_CARD = "card";
    /**
    * List products on one line.
    */
    public const PRODUCT_CARD_LIST_OPTION_LIST = "list";

    /**
    * Shipping selected as collection.
    */
    public const SHIPPING_OPTION_COLLECTION = "collection";
    /**
    * Shipping selected as delivery.
    */
    public const SHIPPING_OPTION_DELIVERY = "delivery";
    /**
    * @var ShortText $username
    * Username
    */
    public ShortText $username;
    /**
    * @var ShortText $name
    * Name
    */
    public ShortText $name;
    /**
    * @var ShortText $surname
    * Surname
    */
    public ShortText $surname;
    /**
    * @var File $profile_photo
    * User profile photo.
    */
    public File $profile_photo;
    /**
    * @var ShortText $email
    * Email
    */
    public ShortText $email;
    /**
    * @var ShortText $phone
    *
    */
    public ShortText $phone;

    public EntityReference $roles;
    public EntityReference $authorized_branch;

    /**
    * @var ShortText $password
    * Hashed user password
    */
    public ShortText $password;
    /**
    * @var Checkbox $active
    * User is active or blocked.
    */
    public Checkbox $active;
    /**
    * @var DateTime $last_access
    *
    */
    public DateTime $last_access;
    /**
    * @var Checkbox $email_verified
    * Is email verified.
    */
    public Checkbox $email_verified;
    /**
    * @var ShortText $email_verification_key
    *
    */
    public ShortText $email_verification_key;
    /**
    * @var EnumaratedList $product_card_list_option
    * Option for listing products as card or list.
    */
    public EnumaratedList $product_card_list_option;
     /**
    * @var EnumaratedList $favorite_card_list_option
    * Option for listing favorites as card or list.
    */
    public EnumaratedList $favorite_card_list_option;
    /**
    * @var EnumaratedList $bespoke_card_list_option
    * Option for listing bespokes as card or list.
    */
    public EnumaratedList $bespoke_card_list_option;
    /**
    * @var EnumaratedList $shipping_option
    * User's selected shipping option.
    */
    public EnumaratedList $shipping_option;
    /**
    * @var TableReference $shipping_branch
    * If user selected shipping option as collection, must select shipping branch as well.
    */
    public TableReference $shipping_branch;
    /**
    * @var DateTime $delivery_date
    * Delivery date.
    */
    public DateTime $delivery_date;
    /**
    * @var TableReference $shipping_address
    * Shipping address reference.
    */
    public TableReference $shipping_address;
    /**
    * @var Checkbox $pay_optional_at_checkout
    * Option to pay or not pay at checkout.
    * When ticked customer gets the option to pay or not to pay at checkout.
    * When unticked customer has to pay at checkout.
    */
    public Checkbox $pay_optional_at_checkout;
    /**
    * @var Integer $special_price_available
    * Special price available above this number.
    */
    public Integer $special_price_available;
    /**
    * @var ShortText $opening_hours
    * Company opening hours.
    */
    public ShortText $opening_hours;
    /**
    * @var TableReference $shop_category
    * Shop category.
    */
    public TableReference $shop_category;
    /**
     * @var Text $comment
     * Users comment.
     */
    public Text $comment;
        /**
    * @var TableReference $comment_last_modified_by
    *
    */
    public TableReference $comment_last_modified_by;
    /**
    * @var DateTime $comment_last_modified_date
    *
    */
    public DateTime $comment_last_modified_date;

    public EntityReference $address;
    public EntityReference $additional_delivery_address;

    public function insert()
    {
        $this->email_verification_key->setValue(
            hash(
                "sha256",
                $this->getFullName() . $this->email->getValue() . microtime()
            )
        );
        if ($result = parent::insert()) {
            if (!IS_CLI) {
                $verifyUrl = VerifyController::getUrl() . "{$this->ID}/" . $this->email_verification_key->getValue();
                \CoreDB::HTMLMail(
                    $this->email->getValue(),
                    Translation::getTranslation("email_verification"),
                    Translation::getEmailTranslation("email_verification", [
                        $this->getFullName(), $verifyUrl, $verifyUrl
                    ]),
                    $this->getFullName()
                );
                \CoreDB::HTMLMail(
                    Variable::getByKey("sales_report_mail")->value->getValue(),
                    Translation::getTranslation("new_user_registered", null, "en"),
                    Translation::getEmailTranslation("new_user_registered", [
                        $this->email->getValue(), $this->editUrl()
                    ], "en"),
                    $this->getFullName()
                );
            }
            return $result;
        } else {
            return false;
        }
    }

    public function delete(): bool
    {
        $this->shipping_address->setValue(null);
        $this->shipping_branch->setValue(null);
        $this->save();
        $orders = Basket::getAll(["user" => $this->ID->getValue()]);
        foreach ($orders as $order) {
            $order->delete();
        }
        \CoreDB::database()->delete(PrivateProductOwner::getTableName())
        ->condition("owner", $this->ID->getValue())
        ->execute();
        \CoreDB::database()->delete(FavoriteProducts::getTableName())
        ->condition("user", $this->ID->getValue())
        ->execute();
        \CoreDB::database()->delete(UserLinkedAccount::getTableName())
        ->condition("sub_Account", $this->ID->getValue())
        ->execute();
        return parent::delete();
    }

    public function actions(): array
    {
        return [
            Link::create(
                InsertController::getUrl(),
                TextElement::create(
                    "<i class='fa fa-plus'></i> " . Translation::getTranslation("add")
                )->setIsRaw(true)
            )->addClass("btn btn-sm btn-primary ms-auto"),
            Link::create(
                AjaxController::getUrl() . "exportUsers?" . http_build_query($_GET),
                TextElement::create(
                    "<i class='fa fa-file-csv'></i> " . Translation::getTranslation("export_users_to_csv")
                )->setIsRaw(true)
            )->addClass("btn btn-sm btn-primary ms-2"),
            Link::create(
                ChangeController::getUrl(),
                TextElement::create(
                    "<i class='fa fa-exchange-alt'></i> " . Translation::getTranslation("change_account_number")
                )->setIsRaw(true)
            )->addClass("btn btn-sm btn-primary ms-2")
        ];
    }

    public function getSearchFormFields(bool $translateLabel = true): array
    {
        $fields = parent::getSearchFormFields($translateLabel);
        $address = new UserAddress();
        $fields = [
            "account_number" => $address->account_number->getSearchWidget()
            ->setName("account_number")
            ->setLabel(Translation::getTranslation("account_number")),
            "address.company_name" => $address->company_name->getSearchWidget()
            ->setName("address.company_name")
            ->setLabel(Translation::getTranslation("company_name")),
            "postalcode" => $address->postalcode->getSearchWidget()
            ->setName("postalcode")
            ->setLabel(Translation::getTranslation("postalcode")),
            "address.phone" => $address->phone->getSearchWidget()
            ->setName("address.phone")
            ->setLabel(Translation::getTranslation("phone"))
        ];
        $fields["address"] = $address->address->getSearchWidget()
        ->setName("address")
        ->setLabel(Translation::getTranslation("address"));
        return $fields;
    }

    public function getResultHeaders(bool $translateLabel = true): array
    {
        $controller = \CoreDB::controller();
        $controller->addJsFiles("ecommerce_theme/src/components/user_comment/user_comment.js");
        $controller->addFrontendTranslation("comment");
        $controller->addFrontendTranslation("last_modified_message");
        $controller->addFrontendTranslation("save");
        $headers = [
            "edit_actions" => "ID",
            "name" => Translation::getTranslation("name"),
            "surname" => Translation::getTranslation("surname"),
            "email" => Translation::getTranslation("email"),
            "company_name" => Translation::getTranslation("company_name"),
            "account_number" => Translation::getTranslation("account_number"),
            "comment" => Translation::getTranslation("comment"),
            "address" => Translation::getTranslation("address"),
            "postalcode" => Translation::getTranslation("postalcode"),
            "phone" => Translation::getTranslation("phone"),
            "registration_date" => Translation::getTranslation("registration_date")
        ];
        return $headers;
    }

    public function getResultQuery(): SelectQueryPreparerAbstract
    {
        return \CoreDB::database()->select(static::getTableName(), "u")
            ->leftjoin(UserAddress::getTableName(), "address", "u.ID = address.user")
            ->leftjoin("users_roles", "ur", "ur.user_id = u.ID")
            ->leftjoin(Role::getTableName(), "roles", "roles.ID = ur.role_id")
            ->select("u", [
                "ID AS edit_actions",
                "name",
                "surname",
                "email"
            ])
            ->select("address", ["company_name", "account_number"])
            ->select("u", ["comment"])
            ->select("address", [
                "address",
                "postalcode",
                "phone"
            ])->select("u", ["created_at AS registration_date"])
            ->select("u", ["comment_last_modified_by", "comment_last_modified_date"])
            ->groupBy("address.account_number")
            ->orderBy("registration_date DESC");
    }

    public function getForm()
    {
        return new UserInsertForm($this);
    }

    protected function getFieldWidget(string $field_name, bool $translateLabel): ?View
    {
        if ($field_name == "address") {
            /** @var CollapsableWidgetGroup */
            $widgetGroup = parent::getFieldWidget($field_name, $translateLabel);
            $widgetGroup->fieldGroup->fields[0]->title = Translation::getTranslation("registered_address");
            return $widgetGroup;
        } else {
            return parent::getFieldWidget($field_name, $translateLabel);
        }
    }

    public function postProcessRow(&$row): void
    {
        $row["registration_date"] = date("d-m-Y H:i:s", strtotime($row["registration_date"]));
        $row['comment'] = Link::create(
            "#",
            ViewGroup::create("i", "fa fa-comment")
        )->addClass("btn btn-sm user-comment-button")
        ->addClass($row["comment"] ? "btn-danger" : "btn-info")
        ->addAttribute("data-user", $row["edit_actions"])
        ->addAttribute("data-comment", $row['comment'] ?: "");
        /** @var AppUser */
        $lastModifiedBy = $row["comment_last_modified_by"] ?
        AppUser::get($row["comment_last_modified_by"]) : null;
        if ($lastModifiedBy) {
            $row['comment']
            ->addAttribute("data-modified-by", $lastModifiedBy->getFullName())
            ->addAttribute(
                "data-modified-date",
                date("d-m-Y H:i:s", strtotime($row["comment_last_modified_date"]))
            );
        }
        unset(
            $row["comment_last_modified_by"],
            $row["comment_last_modified_date"]
        );
        parent::postProcessRow($row);
    }

    public function editUrl($value = null)
    {
        if (!$value) {
            $value = $this->ID->getValue();
        }
        return InsertController::getUrl() . $value;
    }

    public static function generateUsername(string $email)
    {
        $mailStart = explode("@", $email)[0];
        $tempUserName = $mailStart;
        while (User::getUserByUsername($tempUserName)) {
            $tempUserName = $mailStart . random_int(0, 100);
        }
        return $tempUserName;
    }
}
