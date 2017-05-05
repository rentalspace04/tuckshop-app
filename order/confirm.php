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

    function generateForm() {
        global $user;

        $out = "<form id=\"placeOrderForm\" data-user-type=\"$user->type\">\n<table><tbody>\n";

        if ($user->type == User::$PARENT) {
            $out .= orderFor();
        }

        $out .= paymentInput();
        $out .= pickupTime();
        $out .= "<tr><td id=\"confirmButtonRow\" colspan=\"2\">";
        $out .= "<submit id=\"placeOrderButton\" class=\"button\">Place Order</a></form>\n";
        $out .= "</td></tr></tbody></table>\n";

        return $out;
    }

    function paymentInput() {
        $out = "";
        $out .= "<tr>\n<td><label for=\"paymentType\">Payment Type</label></td>\n";
        $out .= "<td><select name=\"paymentType\" id=\"paymentType\" class=\"dropdown\">";

        $out .= "<option>--</option>\n";
        foreach (paymentInputOptions() as $optionID => $optionText) {
            $out .= "<option data-payment-id=\"$optionID\">$optionText</option>\n";
        }
        $out .= "</select></td></tr>\n";

        return $out;
    }

    function paymentInputOptions() {
        global $user;
        $out = array();

        $out["paypal"] = "Paypal";
        $out["credit"] = "Credit Card";
        $out["cash"] = "Cash (on pickup)";

        switch ($user->type) {
            case User::$CHILD:
                $out["allowance"] = "Allowance";
                break;
            case User::$PARENT:
                $out["balance"] = "My Balance";
                $out["allowance"] = "Child's Allowance";
                break;
        }
        return $out;
    }

    function pickupTime() {
        $out = "";
        $out .= "<tr>\n<td><label for=\"pickupTime\">Pickup Time</label></td>\n";

        $out .= "<td><select name=\"pickupTime\" id=\"pickupTime\" class=\"dropdown\">";

        $orderTimes = Helper::getOrderTimes();
        foreach ($orderTimes as $time) {
            $out .= "<option>$time</option>\n";
        }

        $out .= "</select></td></tr>\n";

        return $out;
    }

    function orderFor() {
        global $user;

        if ($user->type != User::$PARENT) {
            return "";
        }

        $out = "";
        $out .= "<tr>\n<td><label for=\"orderFor\">Order For</label></td>\n";

        $out .= "<td><select name=\"orderFor\" id=\"orderFor\" class=\"dropdown\">";

        // If there's more than 1 child, put in a '--' option
        if (count($user->children) > 1) {
            $out .= "<option data-child-id=\"none\">--</option>\n";
        }

        foreach (getParentsChildren() as $child) {
            $name = "$child->firstName $child->lastName";
            $out .= "<option data-child-id=\"$child->userID\">$name</option>\n";
        }

        $out .= "</select></td></tr>\n";

        return $out;
    }

    function getParentsChildren() {
        global $user;

        $children = array();

        foreach ($user->children as $childID) {
            $children[] = User::getById($childID);
        }

        return $children;
    }

    // Setup that this is the Home page
    $page = new Page("Your Order", $user);
    $page->addMenuItem("/order/index.php", "Change<br />Order");
    $page->addMenuItem("/index.php", "Cancel<br />Order");
    $page->addMenuItem("/logout.php", "Log<br />Out");

    $page->addScript("/js/class.js");
    $page->addScript("/js/confirm.js");

    include_once $_SERVER['DOCUMENT_ROOT'] . "/templates/main_start.php"

?>
    <h1>Confirm Order</h1>

    <div id="order-message-container">
        <p id="order-message"></p>
    </div>

    <?php echo generateForm(); ?>

    <p class="centered">

    </p>

<?php

    include_once $_SERVER['DOCUMENT_ROOT'] . "/templates/main_end.php"
?>