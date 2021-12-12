<?php

namespace App\Controller;

use App\Entity\Basket\Basket;
use CoreDB\Kernel\ServiceController;
use Src\Entity\Variable;

class CronController extends ServiceController
{
    public function checkAccess(): bool
    {
        $cron_key = Variable::getByKey("cron_key");
        if (!$cron_key) {
            $cron_key = Variable::create("cron_key");
            $cron_key->map([
                "type" => "text",
                "value" => bin2hex(random_bytes(10)) // 20 characters, only 0-9a-f
            ]);
            $cron_key->save();
        }
        return isset($_GET["cron_key"]) ? $cron_key->value == $_GET["cron_key"] : false;
    }

    public function checkExpiredBaskets()
    {
        $expiredCheckedOutBaskets = \CoreDB::database()
        ->select(Basket::getTableName(), "b")
        ->condition("is_ordered", 0)
        ->condition("is_checked_out", 1)
        ->condition(
            "checkout_time",
            date("Y-m-d H:i:s", strtotime("-20 minutes")),
            "<"
        )
        ->select("b", ["ID"])
        ->execute()->fetchAll(\PDO::FETCH_COLUMN);
        foreach ($expiredCheckedOutBaskets as $basketId) {
            /** @var Basket */
            $basket = Basket::get($basketId);
            $basket->uncheckout();
        }
    }
}
