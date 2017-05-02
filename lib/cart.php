<?php

    include_once $_SERVER['DOCUMENT_ROOT'] . "/lib/helper.php";

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

        public static function fromJSON($json) {
            //
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