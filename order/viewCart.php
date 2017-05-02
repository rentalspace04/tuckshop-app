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

    $user = Helper::getLoggedInUser();

    // Setup that this is the Home page
    $page = new Page("New Order", $user);

    include_once $_SERVER['DOCUMENT_ROOT'] . "/templates/main_start.php"

?>
    <h1>Page Under Construction</h1>
<?php
    include_once $_SERVER['DOCUMENT_ROOT'] . "/templates/main_end.php"
?>