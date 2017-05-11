<?php

    session_start();

    include_once $_SERVER['DOCUMENT_ROOT'] . "/lib/helper.php";
    include_once $_SERVER['DOCUMENT_ROOT'] . "/lib/user.php";

    header("Content-Type: application/json; charset=UTF-8");

    $result = new stdClass();
    $result->okay = false;

    function checkPostData() {
        if (isset($_POST["userType"])) {
            $type = $_POST["userType"];
            if ($type == "parent") {
                return checkParentData();
            } else if ($type == "student") {
                return checkStudentData();
            }
        }
        return false;
    }

    function checkParentData() {
        return isset($_POST["firstname"]) && isset($_POST["lastname"])
            && isset($_POST["firstname"]) && isset($_POST["firstname"])
            && isset($_POST["firstname"]) && isset($_POST["firstname"])
            && isset($_POST["firstname"]) && isset($_POST["firstname"]);
    }

    function checkStudentData() {
        return isset($_POST["firstname"]) && isset($_POST["lastname"])
            && isset($_POST["firstname"]) && isset($_POST["firstname"])
            && isset($_POST["firstname"]) && isset($_POST["firstname"])
            && isset($_POST["firstname"]) && isset($_POST["firstname"]);
    }

    checkPostData();

    echo json_encode($result);

?>