<?php
    session_start();

    include_once $_SERVER['DOCUMENT_ROOT'] . "/lib/page.php";
    include_once $_SERVER['DOCUMENT_ROOT'] . "/lib/helper.php";
    include_once $_SERVER['DOCUMENT_ROOT'] . "/lib/items.php";
    include_once $_SERVER['DOCUMENT_ROOT'] . "/lib/cart.php";

    // Check that we aren't logged in - if we are, redirect to home.php
    if (!Helper::isLoggedIn()) {
        Helper::redirect("/login.php?redirect=/order/index.php");
    }

    $user = Helper::getLoggedInUser();

    // Setup that this is the Home page
    $page = new Page("New Order", $user);

    $page->addScript("/js/class.js");
    $page->addScript("/js/order.js");

    $page->addMenuItem("/order/viewCart.php", "Place<br />Order");
    $page->addMenuItem("/index.php", "Cancel<br />Order");
    $page->addMenuItem("/logout.php", "Log<br />Out");

    if (!isset($_SESSION["cart"])) {
        $cart = new Cart();
        $_SESSION["cart"] = serialize($cart);
    }

    function categoryParagraph($category, $isSelected = false) {
        $selectClass = $isSelected ? "category-selected" : "";
        $para = "<p><a class=\"category-link $selectClass\" data-cat-id=\"$category->categoryID\">$category->name</a></p>\n";
        return $para;
    }

    include_once $_SERVER['DOCUMENT_ROOT'] . "/templates/main_start.php"

?>

<h1 class="inline">Menu</h1>
<a id="cart-link" href="/order/viewCart.php"></a>
<div id="menu">
    <div id="menu-categories">
        <h2>Categories</h2>
        <?php
            $categories = Category::getAllCategories();
            echo categoryParagraph($ALL_CATEGORIES, true);
            foreach ($categories as $cat) {
                echo categoryParagraph($cat);
            }
        ?>
    </div>
    <div id="menu-list">
    </div>
</div>

<?php
    include_once $_SERVER['DOCUMENT_ROOT'] . "/templates/main_end.php"
?>