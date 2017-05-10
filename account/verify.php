<?php
    session_start();

    include_once $_SERVER['DOCUMENT_ROOT'] . "/lib/page.php";
    include_once $_SERVER['DOCUMENT_ROOT'] . "/lib/helper.php";
    include_once $_SERVER['DOCUMENT_ROOT'] . "/lib/user.php";

    // Check that we aren't logged in - if we are, redirect to home.php
    if (Helper::isLoggedIn()) {
        Helper::redirect("/home.php");
    }

    // Setup that this is the Home page
    $page = new Page("Verify Account", NULL);

    // $page->addScript("/js/class.js");
    $page->addScript("/js/verifyAccount.js");

    include_once $_SERVER['DOCUMENT_ROOT'] . "/templates/main_start.php";


    include_once $_SERVER['DOCUMENT_ROOT'] . "/templates/main_end.php";
?>