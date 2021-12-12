<?php

namespace App\Entity\Basket;

use App\Controller\Admin\VoucherController;
use CoreDB\Kernel\Model;
use CoreDB\Kernel\Database\DataType\ShortText;
use CoreDB\Kernel\Database\DataType\FloatNumber;
use CoreDB\Kernel\Database\DataType\Checkbox;
use CoreDB\Kernel\Database\DataType\Date;
use CoreDB\Kernel\Database\DataType\EnumaratedList;
use CoreDB\Kernel\EntityReference;
use Src\Entity\Translation;
use Src\Views\Link;
use Src\Views\TextElement;

/**
 * Object relation with table voucher_codes
 * @author murat
 */

class VoucherCode extends Model
{
     /**
    * TYPE_PERCENTAGE description.
    */
    public const TYPE_PERCENTAGE = "percentage";
    /**
    * TYPE_EXACT_DISCOUNT description.
    */
    public const TYPE_EXACT_DISCOUNT = "exact_discount";

    /**
    * @var ShortText $title
    * Title of code. (Using for administration.)
    */
    public ShortText $title;
    /**
    * @var ShortText $stockcode
    * Stockcode used for billing integration.
    */
    public ShortText $stockcode;
    /**
    * @var ShortText $code
    * Code that must be entered by user on checkout.
    */
    public ShortText $code;
    /**
    * @var EnumaratedList $type
    * Type of voucher codes.
    */
    public EnumaratedList $type;
    /**
    * @var FloatNumber $discount_percentage
    * Percentage of discount that must apply when used.
    */
    public FloatNumber $discount_percentage;
    /**
    * @var FloatNumber $exact_discount
    * Exact discount of code.
    */
    public FloatNumber $exact_discount;
    /**
    * @var Checkbox $always_available
    * If checked no need to enter start date and end date, code is always available.
    */
    public Checkbox $always_available;
    /**
    * @var Date $start_date
    * Code availability start date.
    */
    public Date $start_date;
    /**
    * @var Date $end_date
    * Code availability end date.
    */
    public Date $end_date;

    public EntityReference $voucher_codes_user;

    /**
     * @inheritdoc
     */
    public static function getTableName(): string
    {
        return "voucher_codes";
    }

    public function actions(): array
    {
        return [
            Link::create(
                VoucherController::getUrl() . "add",
                TextElement::create(
                    "<i class='fa fa-plus'></i> " . Translation::getTranslation("add")
                )->setIsRaw(true)
            )->addClass("btn btn-sm btn-primary")
        ];
    }

    public function editUrl($value = null)
    {
        return VoucherController::getUrl() . ($value ?: $this->ID);
    }

    public function postProcessRow(&$row): void
    {
        parent::postProcessRow($row);
        $row["always_available"] = $row["always_available"] ?
        Translation::getTranslation("always_available") : null;
    }

    public function getForm()
    {
        \CoreDB::controller()->addJsCode("
            $(function(){
                $('select[name=\'voucher_code[type]\']').on('change', function(){
                    let percInput = $('input[name=\'voucher_code[discount_percentage]\']');
                    let exactInput = $('input[name=\'voucher_code[exact_discount]\']');
                    switch(this.value){
                        case 'percentage':
                            percInput.parent('.input_widget').show();
                            exactInput.parent('.input_widget').hide();
                            break;
                        case 'exact_discount':
                            percInput.parent('.input_widget').hide();
                            exactInput.parent('.input_widget').show();
                            break;
                    }
                }).trigger('change');
            })
        ");
        return parent::getForm();
    }
}
