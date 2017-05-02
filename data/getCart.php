<?php

    session_start();

    include_once $_SERVER['DOCUMENT_ROOT'] . "/lib/helper.php";
    include_once $_SERVER['DOCUMENT_ROOT'] . "/lib/cart.php";

    header("Content-Type: application/json; charset=UTF-8");

    // Check that we aren't logged in - if we are, redirect to home.php
    if (!Helper::isLoggedIn()) {
        echo "{}";
        exit();
    }

    if (!isset($_SESSION["cart"])) {
        echo "{}";
        exit();
    }

    $cart = unserialize($_SESSION["cart"]);

    echo $cart->toJSON();
?>