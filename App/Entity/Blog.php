<?php

namespace App\Entity;

use App\Controller\Admin\Blog\InsertController;
use CoreDB\Kernel\Model;
use CoreDB\Kernel\Database\DataType\ShortText;
use CoreDB\Kernel\Database\DataType\LongText;
use CoreDB\Kernel\Database\DataType\Checkbox;
use CoreDB\Kernel\Database\DataType\File;
use CoreDB\Kernel\Database\SelectQueryPreparerAbstract;
use CoreDB\Kernel\EntityReference;
use CoreDB\Kernel\XMLSitemapEntityInterface;
use CoreDB\Kernel\XMLSitemapUrl;
use Src\Entity\Translation;
use Src\Theme\View;
use Src\Views\Link;
use Src\Views\TextElement;

/**
 * Object relation with table blogs
 * @author murat
 */

class Blog extends Model implements XMLSitemapEntityInterface
{
    /**
    * @var File $cover_image
    * Blog's cover image.
    */
    public File $cover_image;
    /**
    * @var ShortText $url_alias
    * Url alias of this blog entry.
    */
    public ShortText $url_alias;
    /**
    * @var ShortText $title
    * Blog title.
    */
    public ShortText $title;
    /**
    * @var LongText $content
    * Blog content.
    */
    public LongText $content;
    /**
    * @var Checkbox $published
    * Is blog published.
    */
    public Checkbox $published;

    public EntityReference $blog_attachment;

    /**
     * @inheritdoc
     */
    public static function getTableName(): string
    {
        return "blogs";
    }

    public static function getByUrlAlias($url_alias)
    {
        return static::get(["url_alias" => $url_alias]);
    }

    public function getResultHeaders(bool $translateLabel = true): array
    {
        $headers = parent::getResultHeaders($translateLabel);
        unset(
            $headers["ID"],
            $headers["url_alias"],
            $headers["cover_image"],
            $headers["content"]
        );
        return $headers;
    }

    public function getResultQuery(): SelectQueryPreparerAbstract
    {
        $query = \CoreDB::database()->select(self::getTableName())
        ->select(static::getTableName(), [
            "ID AS edit_actions", "title", "published", "created_at", "last_updated"
        ]);
        return $query;
    }

    public function actions(): array
    {
        return [
            Link::create(
                InsertController::getUrl(),
                TextElement::create(
                    "<i class='fa fa-plus'></i> " .
                    Translation::getTranslation("add")
                )->setIsRaw(true)
            )->addClass("d-sm-inline-block btn btn-sm btn-primary shadow-sm mr-1 mb-1")
        ];
    }

    protected function getFieldWidget(string $field_name, bool $translateLabel): ?View
    {
        if ($field_name == "url_alias") {
            return null;
        } else {
            return parent::getFieldWidget($field_name, $translateLabel);
        }
    }

    public function editUrl($value = null)
    {
        return InsertController::getUrl() . ( $value ?: $this->ID );
    }

    public function save()
    {
        $this->url_alias->setValue($this->generateUrlAlias());
        return parent::save();
    }

    protected function generateUrlAlias()
    {
        $urlAlias = urlencode(
            preg_replace(
                "/[^a-z0-9_]/",
                "",
                mb_strtolower(
                    str_replace(" ", "_", $this->title)
                )
            )
        );
        $tempUrlAlias = $urlAlias;
        $tempCount = 1;
        $blog = Blog::getByUrlAlias($tempUrlAlias);
        while ($blog && $blog->ID->getValue() != $this->ID->getValue()) {
            $tempUrlAlias = $urlAlias . "-" . $tempCount;
            $tempCount++;
            $blog = Blog::getByUrlAlias($tempUrlAlias);
        }
        return $tempUrlAlias;
    }

    public static function getXmlSitemapUrls(): array
    {
        return \CoreDB::database()->select(Blog::getTableName(), "b")
        ->condition("published", 1)
        ->selectWithFunction([
            "CONCAT('" . BASE_URL . "', '/blogs/blog/', b.url_alias) AS loc",
            "DATE_FORMAT(b.last_updated, '%Y-%m-%d') AS lastmod"
        ])->execute()->fetchAll(\PDO::FETCH_CLASS, XMLSitemapUrl::class);
    }
}
