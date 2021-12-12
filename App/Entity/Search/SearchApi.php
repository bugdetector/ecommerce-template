<?php

namespace App\Entity\Search;

use CoreDB\Kernel\Model;
use CoreDB\Kernel\Database\DataType\ShortText;
use CoreDB\Kernel\Database\DataType\Integer;
use Src\Entity\Cache;

/**
 * Object relation with table search_api
 * @author murat
 */

class SearchApi extends Model
{
    /**
    * @var ShortText $word
    * Search word.
    */
    public ShortText $word;
    /**
    * @var Integer $search_count
    * Count that word has been searched.
    */
    public Integer $search_count;

    /**
     * @inheritdoc
     */
    public static function getTableName(): string
    {
        return "search_api";
    }

    public static function set(string $word): ?SearchApi
    {
        try {
            $searchApi = self::get(["word" => $word]) ?: new SearchApi();
            $searchApi->word->setValue($word);
            $searchApi->search_count->setValue(
                $searchApi->search_count->getValue() + 1
            );
            $searchApi->save();
            return $searchApi;
        } catch (\Exception $ex) {
            return null;
        }
    }

    public static function getSearchResult(string $word)
    {
        $cache = Cache::getByBundleAndKey("search_result", $word);
        if (!$cache) {
            Cache::set(
                "search_result",
                $word,
                json_encode(
                    self::getSearchResultByLevenstein($word)
                )
            );
            $cache = Cache::getByBundleAndKey("search_result", $word);
        }
        return json_decode($cache->value->getValue());
    }

    public static function getSearchResultByLevenstein(string $word, bool $useLike = true): array
    {
        $query = \CoreDB::database()->select(self::getTableName(), "sa")
        ->select("sa", ["word", "search_count"])
        ->limit(100)
        ->orderBy("search_count DESC");
        if ($useLike) {
            $query->condition("word", "%$word%", "LIKE");
        }
        $searhWords = $query->execute();
        $searchDistances = [];
        while ($searchWord = $searhWords->fetchObject()) {
            $searchDistances[$searchWord->word] = levenshtein(
                mb_strtolower($word),
                mb_strtolower($searchWord->word),
                0,
                2,
                1
            ) / ($useLike ? $searchWord->search_count : 1);
        }
        asort($searchDistances);
        return array_slice($searchDistances, 0, 10);
    }

    public function delete(): bool
    {
        if ($cache = Cache::getByBundleAndKey("search_result", $this->word)) {
            $cache->delete();
        }
        return parent::delete();
    }
}
