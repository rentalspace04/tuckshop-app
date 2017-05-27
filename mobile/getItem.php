<?php

    include_once $_SERVER['DOCUMENT_ROOT'] . "/lib/token.php";
    include_once $_SERVER['DOCUMENT_ROOT'] . "/lib/user.php";
    include_once $_SERVER['DOCUMENT_ROOT'] . "/lib/items.php";
    include_once $_SERVER['DOCUMENT_ROOT'] . "/lib/helper.php";

    header("Content-Type: application/json; charset=UTF-8");
    header("Access-Control-Allow-Origin: *");
    header('Access-Control-Allow-Methods: GET, POST, PATCH, PUT, DELETE, OPTIONS');
    header('Access-Control-Expose-Headers: x-json');
    header('Access-Control-Allow-Headers: x-prototype-version, x-requested-with, Origin, Content-Type');

    $jsonObj = new stdClass();
    $jsonObj->auth = false;

    // Check that all info is given
    function checkAuthData() {
        if (isset($_POST["token"]) && isset($_POST["userID"])) {
            return Helper::isInteger($_POST["userID"]);
        }
        return false;
    }

    if (checkAuthData()) {
        $token = urldecode($_POST["token"]);
        $userID = $_POST["userID"];

        if (Token::checkAppAuthToken($userID, $token)) {
            $jsonObj->auth = true;
            if (isset($_POST["itemID"]) && Helper::isInteger($_POST["itemID"])) {
                $itemID = $_POST["itemID"];
                if (Item::itemIdExists($itemID)) {
                    $jsonObj->item = Item::getItemById($itemID);
                }
            }

        }
    }

    echo json_encode($jsonObj);

?>