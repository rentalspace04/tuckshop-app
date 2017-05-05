<?php

    include_once $_SERVER['DOCUMENT_ROOT'] . "/lib/helper.php";
    include_once $_SERVER['DOCUMENT_ROOT'] . "/lib/items.php";

    class Cart {
        private $items = array(); // Array of item IDs mapped to their quantities

        public function addItem($item, $quantity = 1) {
            if (isset($this->items[$item])) {
                $this->items[$item] += $quantity;
            } else {
                $this->items[$item] = $quantity;
            }
        }

        public function removeItem($item, $quantity = 1) {
            if (isset($this->items[$item])) {
                $this->items[$item] -= $quantity;
                if ($this->items[$item] <= 0) {
                    unset($this->items[$item]);
                }
            }
        }

        public function toJSON() {
            $json = new stdClass();
            $json->items = $this->items;

            return json_encode($json);
        }

        public function toString() {
            $s = "Items:\n";
            if (!count($this->items)) {
                $s .= " No Items in Order\n";
            }
            foreach ($this->items as $item => $quantity) {
                $s .= " - Item [$item]: $quantity\n";
            }
            return $s;
        }

        public function itemQuantity($itemID) {
            if (!isset($this->items[$itemID])) {
                return 0;
            }
            return $this->items[$itemID];
        }

        public function getItemMap() {
            return $this->items;
        }

        public function totalCost() {
            $cost = 0;
            foreach ($this->items as $itemID => $quantity) {
                if (Item::itemIdExists($itemID)) {
                    $item = Item::getItemById($itemID);
                    $cost += $item->price * $quantity;
                }
            }
            return $cost;
        }

        public function hasItems() {
            return count($this->items) > 0;
        }

        // Checks that all the carts items exist, and that it does have items
        public function isValid() {
            $valid = true;
            foreach ($this->items as $itemID => $q) {
                $valid = $valid & Item::itemIdExists($itemID);
            }
            return $valid && $this->hasItems();
        }

        public function submitAsNewOrder($pdo, $by, $for, $time) {
            // INSERT the order (overall)
            $query = "INSERT INTO Orders (madeBy, madeFor, atTime) VALUES (?, ?, ?)";
            $stmt = $pdo->prepare($query);
            $stmt->execute([$by->userID, $for->userID, $time]);

            // If the statement didn't insert any rows, finish now
            $inserted = $stmt->rowCount();
            if (!$inserted) {
                echo "inserted $inserted rows";
                return false;
            }

            // Get the id of the order inserted just now
            $orderID = $pdo->lastInsertId();

            // INSERT the order items
            $query = "INSERT INTO OrderItems (orderID, itemID, stateID, notes) VALUES (?, ?, ?, ?)";
            $stmt = $pdo->prepare($query);

            $totalItems = 0;
            $itemsInserted = 0;

            // Insert all of the order items - separate items (i.e. quantity
            // of 3 pies means 3 pie order items)
            foreach ($this->items as $itemID => $quantity) {
                for ($i = 0; $i < $quantity; $i++) {
                    // We should be inserting 1 row
                    $totalItems++;
                    // Put in a new order item
                    $stmt->execute([$orderID, $itemID, 1, ""]);
                    // Check how many rows were inserted
                    $itemsInserted += $stmt->rowCount();
                }
            }

            if ($itemsInserted != $totalItems) {
                echo "( $itemsInserted / $totalItems )";
            }

            return $itemsInserted == $totalItems;
        }

        public static function fromJSON($json) {
            $cart = new Cart();

            $jsonObj = json_decode($json);
            $items = $jsonObj->items;

            foreach ($items as $itemID => $quantity) {
                $cart->addItem($itemID, $quantity);
            }

            return $cart;
        }
    }
?>