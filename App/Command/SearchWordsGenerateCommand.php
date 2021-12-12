<?php

namespace App\Command;

use App\Entity\Product\Product;
use App\Entity\Product\ProductCategory;
use App\Entity\Search\SearchApi;
use Exception;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class SearchWordsGenerateCommand extends Command
{
    protected static $defaultName = "search:generate";

    protected function configure()
    {
        $this->setDescription("Generate Search Words for search prediction");
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $titles = \CoreDB::database()->select(Product::getTableName(), "p")
        ->select("p", ["title"])
        ->condition("p.published", 1)
        ->condition("p.is_private_product", 0)
        ->execute();

        $generatedCount = 0;
        while ($title = $titles->fetchColumn()) {
            $searchApi = SearchApi::get([
                "word" => $title
            ]);
            if (!$searchApi) {
                SearchApi::set($title);
                $generatedCount++;
            }
            $filteredTitle = preg_replace(
                "/[^A-Za-z ]/",
                "",
                $title
            );
            $titleParts = array_filter(explode(" ", $filteredTitle));
            $searchParts = [];
            foreach ($titleParts as $part) {
                if (strlen($part) > 3) {
                    $searchParts[] = $part;
                    $searchApi = SearchApi::get([
                        "word" => $part
                    ]);
                    if (!$searchApi) {
                        SearchApi::set($part);
                        $generatedCount++;
                    }
                }
            }
            try {
                $imploded = implode(" ", $searchParts);
                $searchApi = SearchApi::get([
                    "word" => $imploded
                ]);
                if (!$searchApi) {
                    SearchApi::set($imploded);
                    $generatedCount++;
                }
            } catch (Exception $ex) {
                $output->writeln($ex->getMessage());
                $output->writeln($title);
            }
        }

        $categories = \CoreDB::database()->select(ProductCategory::getTableName(), "pc")
        ->select("pc", ["name"])
        ->execute();
        while ($categoryName = $categories->fetchColumn()) {
            $searchApi = SearchApi::get([
                "word" => $categoryName
            ]);
            if (!$searchApi) {
                SearchApi::set($categoryName);
                $generatedCount++;
            }
        }
        $output->writeln($generatedCount . " search words generated.");
        return Command::SUCCESS;
    }
}
