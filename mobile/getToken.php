<?php
    include_once $_SERVER['DOCUMENT_ROOT'] . "/lib/token.php";

    header("Content-Type: application/json; charset=UTF-8");
    header("Access-Control-Allow-Origin: *");

    $jsonObj = new stdClass();

    $jsonObj->token = Token::makeToken();

    echo json_encode($jsonObj);
?>