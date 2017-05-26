<?php

    include_once $_SERVER['DOCUMENT_ROOT'] . "/lib/token.php";
    include_once $_SERVER['DOCUMENT_ROOT'] . "/lib/user.php";
    include_once $_SERVER['DOCUMENT_ROOT'] . "/lib/category.php";

    header("Content-Type: application/json; charset=UTF-8");
    header("Access-Control-Allow-Origin: *");
    header('Access-Control-Allow-Methods: GET, POST, PATCH, PUT, DELETE, OPTIONS');
    header('Access-Control-Expose-Headers: x-json');
    header('Access-Control-Allow-Headers: x-prototype-version, x-requested-with, Origin, Content-Type');

    $jsonObj = new stdClass();
    $jsonObj->auth = false;

    // Check that all info is given
    function checkPostData() {
        return isset($_POST["token"]) && isset($_POST["userID"]);
    }

    if (checkPostData()) {
        $token = urldecode($_POST["token"]);
        $userID = $_POST["userID"];
        if (User::idExists($userID)) {
            if (Token::checkAppAuthToken($userID, $token)) {
                $jsonObj->auth = true;
                $jsonObj->categories = Category::getAllCategories();
            }
        }
    }

    echo json_encode($jsonObj);

?>