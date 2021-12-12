<?php

namespace App\Command;

use App\Entity\Product\Product;
use Exception;
use Src\Entity\Translation;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class UpdateProductUrlAliasCommand extends Command
{
    protected static $defaultName = "product:update-url-alias";

    protected function configure()
    {
        $this->setDescription(
            "Update Products url aliases"
        );
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        try {
            /** @var Product[] $products */
            $products = Product::getAll([]);
            foreach ($products as $product) {
                $product->save();
            }
            $output->writeln(Translation::getTranslation("%d products url alias updated.", [count($products)]));
            return Command::SUCCESS;
        } catch (Exception $ex) {
            $output->writeln($ex->getMessage());
            return Command::FAILURE;
        }
    }
}
