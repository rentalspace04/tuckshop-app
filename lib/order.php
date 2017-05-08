<?php

    include_once $_SERVER['DOCUMENT_ROOT'] . "/lib/helper.php";

    class Order {
        public $orderID;
        public $madeBy;
        public $madeFor;
        public $atTime;
        public $orderedAt;

        // Items will be an array of OrderItems
        public $orderItems = array();

        public function addOrderItems($orderItems) {
            foreach ($orderItems as $oi) {
                $this->orderItems[] = $oi;
            }
        }

        public function getCost() {
            $cost = 0;

            foreach ($this->orderItems as $oi) {
                $cost += $oi->getCost();
            }

            return $cost;
        }

        public function getState() {
            $lowest = null;

            foreach ($this->orderItems as $oi) {
                if (!isset($lowest) || $lowest > $oi->stateID) {
                    $lowest = $oi->stateID;
                }
            }

            return $lowest;
        }

        public function isOwner($userID) {
            return $userID == $this->madeFor || $userID == $this->madeBy;
        }

        // Returns an array of itemID => quantity pairs of items in this order
        public function collectItems() {
            $items = array();
            foreach ($this->orderItems as $oi) {
                if (array_key_exists($oi->itemID, $items)) {
                    $items[$oi->itemID]++;
                } else {
                    $items[$oi->itemID] = 1;
                }
            }
            return $items;
        }

        public static function getOrderByID($id) {
            // Prepare the query and PDO
            $pdo = Helper::tuckshopPDO();
            $query = "SELECT * FROM Orders WHERE orderID=?";
            $statement = $pdo->prepare($query);

            $statement->execute([$id]);
            $orders = $statement->fetchAll(PDO::FETCH_CLASS, 'Order');

            // ID is unique, so there should only be one row
            $order = $orders[0];

            // Get the items in this order
            $query = "SELECT orderItemID FROM OrderItems WHERE orderID=?";
            $statement = $pdo->prepare($query);

            $statement->execute([$id]);
            $orderItemIDs = $statement->fetchAll(PDO::FETCH_COLUMN);
            $orderItems = array();

            foreach ($orderItemIDs as $oiID) {
                $orderItems[] = OrderItem::getOrderItemByID($oiID);
            }

            $order->addOrderItems($orderItems);

            return $order;
        }

        public static function getUserOrderIDs($userID) {
            // Prepare the query and PDO
            $pdo = Helper::tuckshopPDO();
            $query = "SELECT orderID FROM Orders WHERE madeFor=? OR madeBy=? ORDER BY orderedAt DESC";
            $statement = $pdo->prepare($query);

            $statement->execute([$userID, $userID]);
            $orders = $statement->fetchAll(PDO::FETCH_COLUMN);

            return $orders;
        }

        public static function getUserOrderCount($userID) {
            $pdo = Helper::tuckshopPDO();
            $query = "SELECT COUNT(1) FROM Orders WHERE madeFor=? OR madeBy=?";
            $statement = $pdo->prepare($query);

            $statement->execute([$userID, $userID]);
            $numOfOrders = $statement->fetchColumn();

            return $numOfOrders;
        }

        public static function getMostRecentOrder($userID) {
            // Prepare the query and PDO
            $pdo = Helper::tuckshopPDO();

            // Check that the user has indeed placed an order
            if (Order::getUserOrderCount($userID) <= 0) {
                return null;
            }

            $query = "SELECT orderID FROM Orders WHERE madeFor=? OR madeBy=? ORDER BY orderedAt DESC LIMIT 1";
            $statement = $pdo->prepare($query);

            $statement->execute([$userID, $userID]);
            $orderID = $statement->fetchColumn();

            return Order::getOrderByID($orderID);
        }

        public static function orderIdExists($id) {
            $pdo = Helper::tuckshopPDO();
            $query = "SELECT COUNT(*) FROM Orders WHERE orderID=?";
            $statement = $pdo->prepare($query);

            $statement->execute([$id]);
            $matches = $statement->fetchColumn();

            return $matches;
        }
    }

    class OrderItem {
        public $orderItemID;
        public $orderID;
        public $itemID;
        public $stateID;
        public $notes;

        public function getCost() {
            $item = Item::getItemById($this->itemID);
            return $item->price;
        }

        public static function getOrderItemByID($id) {
            // Prepare the query and PDO
            $pdo = Helper::tuckshopPDO();
            $query = "SELECT * FROM OrderItems WHERE orderItemID=?";
            $statement = $pdo->prepare($query);

            $statement->execute([$id]);
            $orderItems = $statement->fetchAll(PDO::FETCH_CLASS, 'OrderItem');

            $orderItem = $orderItems[0];

            return $orderItem;
        }
    }

    class State {
        public $name;
        public $stateID;

        public static function getStateById($id) {
            // Prepare the query and PDO
            $pdo = Helper::tuckshopPDO();
            $query = "SELECT name FROM States WHERE stateID=?";
            $statement = $pdo->prepare($query);

            $statement->execute([$id]);
            $stateName = $statement->fetchColumn();

            return $stateName;
        }
    }
?>