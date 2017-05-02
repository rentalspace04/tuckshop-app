<?php

    session_start();

    include_once $_SERVER['DOCUMENT_ROOT'] . "/lib/helper.php";
    include_once $_SERVER['DOCUMENT_ROOT'] . "/lib/items.php";
    include_once $_SERVER['DOCUMENT_ROOT'] . "/lib/category.php";

    header("Content-Type: application/json; charset=UTF-8");

    // Check that we're logged in - if we are, redirect to home.php
    if (Helper::isLoggedIn()) {
        if (isset($_GET["itemID"]) && Item::itemIdExists($_GET["itemID"])) {
            $itemID = $_GET["itemID"];
            $item = Item::getItemById($itemID);
            echo $item->toJSON();
            exit();
        } else if (isset($_GET["categoryID"]) && (Category::categoryIdExists($_GET["categoryID"]) || $_GET["categoryID"] == 0)) {
            $categoryID = $_GET["categoryID"];
            $items = Category::getItemsByCategory($categoryID);
            $json = new stdClass();
            $json->items = $items;
            echo json_encode($json);
            exit();
        }
    }

    echo "{}";
?>