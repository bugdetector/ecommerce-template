<?php

namespace App\Entity\Page;

use CoreDB\Kernel\Model;
use CoreDB\Kernel\Database\DataType\ShortText;
use CoreDB\Kernel\Database\DataType\LongText;
use CoreDB\Kernel\XMLSitemapEntityInterface;
use CoreDB\Kernel\XMLSitemapUrl;

/**
 * Object relation with table pages
 * @author murat
 */

class Page extends Model implements XMLSitemapEntityInterface
{
    /**
    * @var ShortText $title
    * Page title.
    */
    public ShortText $title;
    /**
    * @var ShortText $url_alias
    * Url alias pattern for this page.
    */
    public ShortText $url_alias;
    /**
    * @var LongText $body
    * Body HTML for page.
    */
    public LongText $body;

    /**
     * @inheritdoc
     */
    public static function getTableName(): string
    {
        return "pages";
    }

    public function getForm()
    {
        \CoreDB::controller()->addJsFiles("dist/file_input/file_input.js");
        \CoreDB::controller()->addCssFiles("dist/file_input/file_input.css");
        \CoreDB::controller()->addFrontendTranslation("close");
        return parent::getForm();
    }

    public static function getXmlSitemapUrls(): array
    {
        return \CoreDB::database()->select(Page::getTableName(), "p")
        ->selectWithFunction([
            "CONCAT('" . BASE_URL . "', '/page/', p.url_alias) AS loc",
            "DATE_FORMAT(p.last_updated, '%Y-%m-%d') AS lastmod"
        ])->execute()->fetchAll(\PDO::FETCH_CLASS, XMLSitemapUrl::class);
    }
}
