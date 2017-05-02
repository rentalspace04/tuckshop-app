<?php

    include_once $_SERVER['DOCUMENT_ROOT'] . "/lib/helper.php";
    include_once $_SERVER['DOCUMENT_ROOT'] . "/lib/items.php";

    class Category {

        public $name;
        public $categoryID;

        public function __construct($id = null, $name = null) {
            if (isset($id) && isset($name)) {
                $this->name = $name;
                $this->categoryID = $id;
            }
        }

        public function getItems() {
            return Category::getItemsByCategory($this->categoryID);
        }

        public function toString() {
            return "$this->name [$this->categoryID]";
        }

        public static function getCategoryById($categoryID) {
            // Prepare the query and PDO
            $pdo = Helper::tuckshopPDO();
            $query = "SELECT * FROM Categories WHERE categoryID=?";
            $statement = $pdo->prepare($query);

            $statement->execute([$categoryID]);
            $category = $statement->fetchAll(PDO::FETCH_CLASS, "Category");

            return $category[0];
        }

        public static function getCategoryByName($name) {
            // Prepare the query and PDO
            $pdo = Helper::tuckshopPDO();
            $query = "SELECT * FROM Categories WHERE name=?";
            $statement = $pdo->prepare($query);

            $statement->execute([$name]);
            $category = $statement->fetchAll(PDO::FETCH_CLASS, "Category");

            return $category[0];
        }

        public static function getCategoryName($categoryID) {
            // Prepare the query and PDO
            $pdo = Helper::tuckshopPDO();
            $query = "SELECT name FROM Categories WHERE categoryID=?";
            $statement = $pdo->prepare($query);

            $statement->execute([$categoryID]);
            $category = $statement->fetchColumn();

            return $category;
        }

        public static function getCategoryID($categoryName) {
            // Prepare the query and PDO
            $pdo = Helper::tuckshopPDO();
            $query = "SELECT categoryID FROM Categories WHERE name=?";
            $statement = $pdo->prepare($query);

            $statement->execute([$categoryName]);
            $id = $statement->fetchColumn();

            // Only get the first one... If there's more than one, something's wrong
            return $id;
        }

        public static function getItemIDsByCategory($categoryID) {
            // Prepare the query and PDO
            $pdo = Helper::tuckshopPDO();
            $query = "SELECT itemID FROM ItemCategories WHERE categoryID=?";
            $statement = $pdo->prepare($query);

            $statement->execute([$categoryID]);
            $itemIDs = $statement->fetchAll(PDO::FETCH_COLUMN);

            return $itemIDs;
        }

        public static function getItemsByCategory($categoryID) {
            // Check if they're asking for the "All" category
            if ($categoryID==0) {
                return Item::getAllItems();
            }
            $itemIDs = Category::getItemIDsByCategory($categoryID);

            $items = array();

            foreach ($itemIDs as $itemID) {
                $items[] = Item::getItemById($itemID);
            }

            return $items;
        }

        public static function getItemCategories($itemID) {
            // Prepare the query and PDO
            $pdo = Helper::tuckshopPDO();
            $query = "SELECT Categories.* FROM ItemCategories INNER JOIN Categories ON Categories.categoryID = ItemCategories.categoryID WHERE itemID=?";
            $statement = $pdo->prepare($query);

            $statement->execute([$itemID]);
            $categoryIDs = $statement->fetchAll(PDO::FETCH_CLASS, "Category");

            return $categoryIDs;
        }

        public static function categoryIdExists($categoryID) {
            // Prepare the query and PDO
            $pdo = Helper::tuckshopPDO();
            $query = "SELECT Count(*) FROM Categories WHERE categoryID=?";
            $statement = $pdo->prepare($query);

            $statement->execute([$categoryID]);
            $categoryExists = $statement->fetchColumn();

            return $categoryExists;
        }

        public static function getAllCategories() {
            // Prepare the query and PDO
            $pdo = Helper::tuckshopPDO();
            $query = "SELECT * FROM Categories";
            $statement = $pdo->prepare($query);

            $statement->execute();
            $categories = $statement->fetchAll(PDO::FETCH_CLASS, "Category");

            return $categories;
        }
    }

    $ALL_CATEGORIES = new Category(0, "All");

 ?>