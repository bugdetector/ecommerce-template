<?php

define("DB_SERVER", "%db_server");
define("DB_NAME", "%db_name");
define("DB_USER", "%db_user");
define("DB_PASSWORD", "%db_password");
define("HASH_SALT", "%hash_salt");

define("TIMEZONE", "Europe/Istanbul");

define("LANGUAGE", "tr");

define("TRUSTED_HOSTS", "localhost,127.0.0.1");

/**
 * production  -> Twig cache enabled, Mails send to exact location.
 * staging     -> Twig cache enabled, Mails send to test mail address.
 * development -> Twig cache disabled, Mails send to test mail address.
 */

define("ENVIROMENT", "development");

/**
 * full_one_device_login  -> All users can login by one device. Other devices sessions will thrown when another device used.
 * role_based_one_device_login -> Defined roles using LOGIN_POLICY_ROLES will be able to login using only one device.
 * no_restrictions -> No restrictions available.
 */

define("LOGIN_POLICY", "no_restrictions");

/**
 * Give roles definitions in an array if LOGIN_POLICY role_based_one_device_login used.
 * Ex: ['Admin', 'User']
 */
define("LOGIN_POLICY_ROLES", []);


// To configure PWA feature use the section below.
if (!IS_CLI) {
    define("PWA_ENABLED", true);
    define(
        "PWA_MANIFEST",
        [
            "name" => "Core DB",
            "short_name" => "C DB",
            "description" => "Best practice web development tool.",
            "start_url" => ".",
            "display" => "standalone",
            "theme_color" => "#fff",
            "background_color" => "#fff",
            "icons" => [
                [
                    "src" => SITE_ROOT . "/assets/square_logo.png",
                    "sizes" => "120x120",
                    "type" => "image/png"
                ],
                [
                    "src" => SITE_ROOT . "/assets/square_logo-512x512.png",
                    "sizes" => "512x512",
                    "type" => "image/png"
                ]
            ]
        ]
    );
}


/**
 * Enter SENTRY_DSN where get it from sentry 
 * Ex:
 * https://example@example.ingest.sentry.io/example
 */
define("SENTRY_DSN", false);

define("PAYMENT_CLIENT", "App\Lib\IsBankClient");
define("OMNIPAY_USERNAME", "username");
define("OMNIPAY_STOREKEY", "storekey");
define("OMNIPAY_CLIENT_ID", "clientid");
define("OMNIPAY_PASSWORD", "password");
define("OMNIPAY_FIRMNAME", "firmname");
define("OMNIPAY_CURRENCY", "TRY");