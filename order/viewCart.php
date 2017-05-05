<?php
    session_start();

    include_once $_SERVER['DOCUMENT_ROOT'] . "/lib/page.php";
    include_once $_SERVER['DOCUMENT_ROOT'] . "/lib/helper.php";
    include_once $_SERVER['DOCUMENT_ROOT'] . "/lib/items.php";
    include_once $_SERVER['DOCUMENT_ROOT'] . "/lib/cart.php";

    // Check that that the user is logged in and that there's an in progress
    // order
    if (!isset($_SESSION["cart"])|| !Helper::isLoggedIn()) {
        // If not, redirect to login page
        Helper::redirect("/login.php?redirect=/order/index.php");
    }

    $cart = unserialize($_SESSION["cart"]);

    $user = Helper::getLoggedInUser();

    // Setup that this is the Home page
    $page = new Page("Your Order", $user);
    $page->addMenuItem("/order/index.php", "Back to<br />Order");
    $page->addMenuItem("/index.php", "Cancel<br />Order");
    $page->addMenuItem("/logout.php", "Log<br />Out");

    function itemRow($itemID, $quantity) {
        if (!Item::itemIdExists($itemID)) {
            // TODO
            return "";
        }
        $item = Item::getItemByID($itemID);
        $out = "";
        $out .= "<tr>\n<td>$item->name</td>\n";
        $out .= "<td>\$$item->price</td>\n";
        $out .= "<td>$quantity</td>\n";
        $subtotal = number_format($quantity * $item->price, 2);
        $out .= "<td>\$$subtotal</td>\n</tr>\n";
        return $out;
    }

    function generateCartList($cart) {
        $out = "<table id=\"cartList\">\n<thead>\n<tr>\n";
        $out .= "<th>Item</th>\n<th>Price</th>\n<th>Quantity</th>\n<th>Subtotal</th>\n";
        $out .= "</tr>\n</thead>\n<tbody>\n";
        foreach ($cart->getItemMap() as $item => $quantity) {
            $out .= itemRow($item, $quantity);
        }
        $out .= "</tbody>";
        $out .= "</table>";
        $cost = number_format($cart->totalCost(), 2);
        $out .= "<h2 id=\"order-total\">Total: <span id=\"order-total-price\">\$$cost</span></h2>";
        $out .= "<div id=\"order-message-container\"><p id=\"order-message\"></p></div>";
        $out .= "<p class=\"centered\"><a href=\"/order/confirm.php\" id=\"continueButton\" class=\"button\">Continue</a></p>";
        return $out;
    }

    include_once $_SERVER['DOCUMENT_ROOT'] . "/templates/main_start.php"

?>
    <h1>Your Order</h1>

<?php

    if ($cart->hasItems()){
        echo generateCartList($cart);
    } else {
        echo "<div class=\"centered\">\n<h4>You don't have any items in your order yet...</h4>\n";
        echo "<p><a class=\"button\", href=\"/order/index.php\">Go back and add some.</a></p>\n</div>";
    }

    include_once $_SERVER['DOCUMENT_ROOT'] . "/templates/main_end.php"
?>