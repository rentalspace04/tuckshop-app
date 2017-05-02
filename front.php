<?php

    session_start();

    include_once $_SERVER['DOCUMENT_ROOT'] . "/lib/page.php";
    include_once $_SERVER['DOCUMENT_ROOT'] . "/lib/helper.php";

    // Check that we aren't logged in - if we are, redirect to home.php
    if (Helper::isLoggedIn()) {
        Helper::redirect("/home.php");
    }

    // Setup that this is the front page
    $page = new Page("Front Page", NULL);

    include_once $_SERVER['DOCUMENT_ROOT'] . "/templates/main_start.php"

?>

            <p class="italics">Tuckr is an online tuckshop ordering app that makes it easy for you to get lunch at your school.</p>
            <div id="front-page-welcome">
                <div id="fpw-sign-in">
                    <p>Want to make an order?</p>
                    <a href="/login.php">Sign In</a>
                </div>
                <div id="fpw-create-account">
                    <p>Don't have an account with us?</p>
                    <a href="template.html">Create an Account</a>
                </div>
            </div>
            <p>You can use Tuckr to check what's on the menu and what's in stock, and make your order quickly and easily.</p>
            <p>If you're a parent, Tuckr gives you the ability to:</p>
            <ul>
                <li>See what your kids are ordering,</li>
                <li>Order for them,</li>
                <li>Give them an allowance (so they can order for themselves), and</li>
                <li>Stop them from being able to order.</li>
            </ul>

<?php
    include_once $_SERVER['DOCUMENT_ROOT'] . "/templates/main_end.php"
?>