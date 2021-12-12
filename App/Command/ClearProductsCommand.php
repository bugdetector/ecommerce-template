<?php

namespace App\Command;

use App\Entity\Product\Product;
use Exception;
use Src\Entity\Translation;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class ClearProductsCommand extends Command
{
    protected static $defaultName = "product:clear-all";

    protected function configure()
    {
        $this->setDescription(
            "Remove all products"
        );
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        try {
            $removeCount = 0;
            /** @var Product $product */
            foreach (Product::getAll([]) as $product) {
                $product->delete();
                $removeCount++;
            }
            $output->writeln(
                Translation::getTranslation(
                    "%d product removed.",
                    [$removeCount]
                )
            );
            return Command::SUCCESS;
        } catch (Exception $ex) {
            $output->writeln($ex->getMessage());
            return Command::FAILURE;
        }
    }
}
