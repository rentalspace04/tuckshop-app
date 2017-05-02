<?php

    include_once $_SERVER['DOCUMENT_ROOT'] . "/lib/helper.php";
    include_once $_SERVER['DOCUMENT_ROOT'] . "/lib/category.php";

    class Item {
        public static $DEFAULT_IMAGE = "/img/default_thumb.png";
        public $itemID;
        public $name;
        public $description;
        public $availability;
        public $price;
        public $prepNeeded;
        public $image;
        public $categories = array();

        public function __construct() {
            if (!isset($this->image) || $this->image == "") {
                $this->image = Item::$DEFAULT_IMAGE;
            }
        }

        public function addCategories($categories) {
            foreach ($categories as $category) {
                $this->categories[] = $category;
            }
        }

        public function getImage() {
            if ($this->image) {
                return $this->image;
            }
            return Item::$DEFAULT_IMAGE;
        }

        public function stringRep() {
            $rep = "<pre>";

            $rep .= "$this->name\n";
            $rep .= "Price: $this->price\n";
            $rep .= "$this->description\n";
            $rep .= "ID: $this->itemID\n";
            $rep .= "Availability: $this->availability\n";
            $rep .= "Image Location: $this->image\n";
            $rep .= "Categories:\n";
            foreach ($this->categories as $cat) {
                $rep .= " - $cat->name\n";
            }

            $rep .= "</pre>";

            return $rep;
        }

        public function toJSON() {
            return json_encode($this);
        }

        public static function getItemById($id) {
            // Prepare the query and PDO
            $pdo = Helper::tuckshopPDO();
            $query = "SELECT * FROM Items WHERE itemID=?";
            $statement = $pdo->prepare($query);

            $statement->execute([$id]);
            $items = $statement->fetchAll(PDO::FETCH_CLASS, 'Item');

            // ID is unique, so there should only be one row
            $item = $items[0];

            $categories = Category::getItemCategories($item->itemID);

            $item->addCategories($categories);

            return $item;
        }

        public static function itemIdExists($itemID) {
            $pdo = Helper::tuckshopPDO();
            $query = "SELECT COUNT(*) FROM Items WHERE itemID=?";
            $statement = $pdo->prepare($query);
            $statement->execute([$itemID]);
            $hasItem = $statement->fetchColumn();

            return $hasItem;
        }

        public static function getAllItems() {
            // Prepare the query and PDO
            $pdo = Helper::tuckshopPDO();
            $query = "SELECT itemID FROM Items";
            $statement = $pdo->prepare($query);

            $statement->execute();
            $itemIDs = $statement->fetchAll(PDO::FETCH_COLUMN);
            $items = array();

            foreach ($itemIDs as $itemID) {
                $items[] = Item::getItemById($itemID);
            }

            return $items;
        }
    }
?>