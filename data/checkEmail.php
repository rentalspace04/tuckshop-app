<?php

    session_start();

    include_once $_SERVER['DOCUMENT_ROOT'] . "/lib/helper.php";
    include_once $_SERVER['DOCUMENT_ROOT'] . "/lib/user.php";

    header("Content-Type: application/json; charset=UTF-8");

    $result = new stdClass();
    $result->okay = false;

    if (isset($_GET["email"])) {
        if (!User::emailExists($_GET["email"])) {
            $result->okay = true;
        }
    }

    echo json_encode($result);

?>