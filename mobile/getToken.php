<?php
    include_once $_SERVER['DOCUMENT_ROOT'] . "/lib/token.php";

    header("Content-Type: application/json; charset=UTF-8");
    header("Access-Control-Allow-Origin: *");
    header('Access-Control-Allow-Methods: GET, POST, PATCH, PUT, DELETE, OPTIONS');
    header('Access-Control-Expose-Headers: x-json');
    header('Access-Control-Allow-Headers: Origin, Content-Type');

    $jsonObj = new stdClass();

    $jsonObj->token = Token::makeToken();

    echo json_encode($jsonObj);
?>