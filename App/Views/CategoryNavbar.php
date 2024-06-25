<?php

namespace App\Views;

use App\Entity\Product\ProductCategory;
use Src\Entity\Cache;
use Src\Views\Navbar;

class CategoryNavbar extends Navbar
{
    public $categories = [];
    public bool $isLoggedIn;
    public function __construct(string $tag_name, string $wrapper_class)
    {
        parent::__construct($tag_name, $wrapper_class);
        $this->isLoggedIn = \CoreDB::currentUser()->isLoggedIn();
        $controller = \CoreDB::controller();
        $controller->addJsFiles("dist/category_navbar/category_navbar.js");
        $controller->addCssFiles("dist/category_navbar/category_navbar.css");
        $this->addAttribute("id", "category-tree");
        if ($cache = Cache::getByBundleAndKey("category_tree", "tree_data")) {
            $this->categories = json_decode($cache->value->getValue(), true);
        } else {
            /** @var ProductCategory $category */
            foreach (ProductCategory::getRootElements() as $category) {
                $this->categories[$category->ID->getValue()] = [
                    "ID" => $category->ID->getValue(),
                    "name" => $category->name->getValue(),
                ];
                $subCategories = $this->getSubCategories($category);
                if ($subCategories) {
                    $this->categories[$category->ID->getValue()]["subCategories"] = $subCategories;
                }
            }
            Cache::set("category_tree", "tree_data", json_encode($this->categories));
        }
    }

    private function getSubCategories(ProductCategory $category)
    {
        $result = null;
        if ($categories = $category->getSubNodes()) {
            $result = [];
            /** @var ProductCategory $category */
            foreach ($categories as $category) {
                $result[$category->ID->getValue()] = [
                    "ID" => $category->ID->getValue(),
                    "name" => $category->name->getValue(),
                ];
                $subCategories = $this->getSubCategories($category);
                if ($subCategories) {
                    $result[$category->ID->getValue()]["subCategories"] = $subCategories;
                }
            }
        }
        return $result;
    }

    public static function create(string $tag_name, string $wrapper_class): Navbar
    {
        return new CategoryNavbar($tag_name, $wrapper_class);
    }
    public function getTemplateFile(): string
    {
        return "category-navbar.twig";
    }
}
