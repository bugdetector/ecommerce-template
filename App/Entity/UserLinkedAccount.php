<?php

namespace App\Entity;

use CoreDB;
use CoreDB\Kernel\Model;
use CoreDB\Kernel\Database\DataType\TableReference;
use Src\Entity\Translation;

/**
 * Object relation with table users_linked_account
 * @author murat
 */

class UserLinkedAccount extends Model
{
    /**
    * @var TableReference $master_account
    * Master table.
    */
    public TableReference $master_account;
    /**
    * @var TableReference $sub_account
    * Referenced account.
    */
    public TableReference $sub_account;

    /**
     * @inheritdoc
     */
    public static function getTableName(): string
    {
        return "users_linked_account";
    }

    protected function insert()
    {
        if ($result = parent::insert()) {
            if (!IS_CLI) {
                /** @var CustomUser */
                $master = CustomUser::get($this->master_account->getValue());
                /** @var CustomUser */
                $subAccount = CustomUser::get($this->sub_account->getValue());
                CoreDB::HTMLMail(
                    $master->email->getValue(),
                    "New User Linked To Your Account",
                    Translation::getEmailTranslation(
                        "new_user_linked",
                        [
                            $subAccount->getFullName(),
                            $subAccount->address->getValue()[0]["company_name"],
                            $subAccount->email->getValue()
                        ],
                        "en"
                    ),
                    $master->getFullName()
                );
            }
            return $result;
        } else {
            return false;
        }
    }
}
