<?php
    session_start();

    include_once $_SERVER['DOCUMENT_ROOT'] . "/lib/page.php";
    include_once $_SERVER['DOCUMENT_ROOT'] . "/lib/helper.php";
    include_once $_SERVER['DOCUMENT_ROOT'] . "/lib/items.php";
    include_once $_SERVER['DOCUMENT_ROOT'] . "/lib/cart.php";

    // User needs to be logged in w/ an in progress order
    if (!Helper::isLoggedIn() || !isset($_SESSION["cart"])) {
        Helper::redirect("/login.php?redirect=/order/index.php");
    }

    $cart = unserialize($_SESSION["cart"]);

    $user = Helper::getLoggedInUser();

    // Check that they gave an item ID
    if (isset($_GET["itemID"]) && Item::itemIdExists($_GET["itemID"])) {
        // Get the item that's being viewed
        $item = Item::getItemById($_GET["itemID"]);
    } else {
        // Setup that this is an error page
        $page = new Page("Item Not Found", $user);
        // Show that there was an error
        include_once $_SERVER['DOCUMENT_ROOT'] . "/templates/main_start.php";

        echo "<h1>There's Been an Error</h1>";
        echo "<p>The requested item wasn't found.</p>";
        echo "<p>The link you followed (or the page you refreshed) might have been old.</p>";
        echo "<p>Please go back and try again</p>";

        include_once $_SERVER['DOCUMENT_ROOT'] . "/templates/main_end.php";
        exit();
    }

    // Setup that this is this item's page
    $page = new Page($item->name, $user);
    $page->addScript("/js/class.js");
    $page->addScript("/js/item.js");
    $page->addMenuItem("/order/index.php", "Back to<br />Order");
    $page->addMenuItem("/index.php", "Cancel<br />Order");
    $page->addMenuItem("/logout.php", "Log<br />Out");

    include_once $_SERVER['DOCUMENT_ROOT'] . "/templates/main_start.php";

?>
<p><a id="backToOrder" href="/order/index.php">&lt;&lt; Back To Order</a></p>
<h1><?php echo $item->name; ?></h1>
<?php echo "<table id=\"item-info\" data-item-id=\"$item->itemID\">"; ?>
    <tbody>
        <tr>
            <td>
                <?php echo "<img src=\"$item->image\">\n";
                    $inStock = $item->availability > 0;
                    if ($inStock) {
                        echo "<p class=\"item-availability\">$item->availability in stock</p>";

                    } else  {
                        echo "<p class=\"item-availability unavailable\">Not currently available</p>";
                    }
                ?>
            </td>
            <td>
                <p class="item-price">$<?php echo $item->price; ?><span class="item-price-each">each</span></p>
                <p class="item-desc"><?php echo $item->description; ?></p>
                <p class="centered">
                    Quantity:
                    <input class="textbox" type="text" id="itemQuantity" tabindex="0">
                    <a id="updateOrder" class="button">Update</a>
                </p>
                <?php
                    if ($cart->itemQuantity($_GET["itemID"])) {
                        echo "<p class=\"centered\"><a id=\"removeFromOrder\" class=\"button\">Remove From Order</a></p>\n";
                    }
                ?>
            </td>
        </tr>
    </tbody>
</table>

<?php
    include_once $_SERVER['DOCUMENT_ROOT'] . "/templates/main_end.php";
?>