<?php

    /*
    *
    *    Takes a JSON object representing the cart's new state in the POST
    *    Updates the Cart's state to reflect this.
    *    Returns a JSON object indicating whether or not the cart was
    *    successfully updated
    *
    */

    session_start();

    include_once $_SERVER['DOCUMENT_ROOT'] . "/lib/helper.php";
    include_once $_SERVER['DOCUMENT_ROOT'] . "/lib/cart.php";

    header("Content-Type: application/json; charset=UTF-8");

    $returnObj = new stdClass();
    $returnObj->success = false;

    // Check that we aren't logged in - if we are, redirect to home.php
    if (Helper::isLoggedIn()) {
        if (isset($_POST["cartState"])) {
            $newCart = Cart::fromJSON($_POST["cartState"]);
            $_SESSION["cart"] = serialize($newCart);
            $returnObj->success = true;
        }

    }

    echo json_encode($returnObj);
?>