<?php
    session_start();

    include_once $_SERVER['DOCUMENT_ROOT'] . "/lib/page.php";
    include_once $_SERVER['DOCUMENT_ROOT'] . "/lib/helper.php";
    include_once $_SERVER['DOCUMENT_ROOT'] . "/lib/items.php";
    include_once $_SERVER['DOCUMENT_ROOT'] . "/lib/order.php";

    // Check that we aren't logged in - if we are, redirect to history page
    if (!Helper::isLoggedIn()) {
        Helper::redirect("/login.php?redirect=/history/index.php");
    }

    $user = Helper::getLoggedInUser();

    function getOrder($user, $id) {
        $result = new stdClass();
        $result->order = null;
        $result->success = false;
        $result->error = 0;
        // Check that the id is a number (i.e. could be valid)
        if (Helper::isInteger($id)) {
            // Check that the id is a valid order
            if (Order::orderIdExists($id)) {
                // Get the order object
                $order = Order::getOrderById($id);
                // Check that the order is one the user has access to
                if ($order->isOwner($user->userID)) {
                    $result->order = $order;
                    $result->success = true;
                } else {
                    $result->error = 2;
                }
            } else {
                $result->error = 1;
            }
        } else {
            $result->error = 1;
        }
        return $result;
    }

    function getContent($order, $user) {
        $out = "";

        $out .= "<h1>Order #$order->orderID</h1>\n";
        $state = State::getStateById($order->getState());
        $stateClass = strtolower(str_replace(" ", "-", $state));
        $out .= "<div id=\"orderState\" class=\"$stateClass\"><p class=\"centered\">$state</p></div>";
        $orderedAt = date("d/m/y h:i A", strtotime($order->orderedAt));
        $out .= "<p>Made: $orderedAt</p>";
        if ($user->type == User::$PARENT) {
            $madeFor = User::getById($order->madeFor);
            $out .= "<p>For: $madeFor->firstName $madeFor->lastName</p>";
        }
        $price = number_format($order->getCost(), 2);
        $out .= "<p>Price: \$$price</p>";
        $out .= makeOrderTable($order);
        $out .= "";

        return $out;
    }

    function makeOrderTable($order) {
        $out = "";

        $out .= "<table id=\"cartList\"><thead><tr>";
        $out .= "<th>Item</th>";
        $out .= "<th>Price</th>";
        $out .= "<th>Quantity</th>";
        $out .= "<th>Subtotal</th></thead><tbody>";
        foreach ($order->collectItems() as $itemID => $quantity) {
            $item = Item::getItemById($itemID);
            $out .= "<tr><td>$item->name</td>";
            $price = number_format($item->price, 2);
            $out .= "<td>\$$price</td>";
            $out .= "<td>$quantity</td>";
            $subtotal = number_format($item->price * $quantity, 2);
            $out .= "<td>$subtotal</td></tr>";

        }
        $out .= "</tbody></table>";

        return $out;
    }

    function getBadIDContent() {
        $out = "<h1>Invalid Request</h1>";

        $out .= "<p>Either there wasn't an Order Number specified, or the requested order doesn't exist. Maybe go back and try oping this page again.</p>";

        return $out;
    }

    function getNotYourOrderContent() {
        $out = "<h1>Invalid Request</h1>";

        $out .= "<p>You don't have access to this order because this order was neither made for or by you.</p>";

        return $out;
    }

    $orderResult = getOrder($user, isset($_GET["id"]) ? $_GET["id"] : "");

    if ($orderResult->success) {
        $order = $orderResult->order;
        // Setup that this is the Home page
        $page = new Page("Order #$order->orderID", $user);
        // $page->addScript("/js/class.js");
        // $page->addScript("/js/history.js");
    } else {
        // Setup that this is the Home page
        $page = new Page("Invalid Request", $user);
    }

    $page->addMenuItem("/index.php", "Home");
    $page->addMenuItem("/history/index.php", "Order<br />History");
    $page->addMenuItem("/logout.php", "Log<br />Out");

    include $_SERVER['DOCUMENT_ROOT'] . "/templates/main_start.php";

    if ($orderResult->success) {
        echo getContent($order, $user);
    } else if ($orderResult->error == 2) {
        echo getNotYourOrderContent();
    } else {
        echo getBadIDContent();
    }
    include $_SERVER['DOCUMENT_ROOT'] . "/templates/main_end.php";
?>