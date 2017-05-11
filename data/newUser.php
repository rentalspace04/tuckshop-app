<?php

    session_start();

    // If any errors occur, assume it hasn't been submitted successfully
    function handle_error($errno, $errstr) {
        $json = new stdClass();
        $json->okay = false;
        $json->message = "Something went wrong";
        $json->error = "[$errno] - $errstr";

        echo json_encode($json);
        exit();
    }

    //set_error_handler("handle_error");

    include_once $_SERVER['DOCUMENT_ROOT'] . "/lib/helper.php";
    include_once $_SERVER['DOCUMENT_ROOT'] . "/lib/user.php";

    header("Content-Type: application/json; charset=UTF-8");

    $result = new stdClass();
    $result->okay = false;
    $result->message = "";
    $result->error = "";

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

    // Returns a data object with the new user's data in it or false (if the
    // data wasn't valid/there)
    function checkParentData() {
        if (isset($_POST["firstname"]) && isset($_POST["lastname"])
            && isset($_POST["email"]) && isset($_POST["phone"])
            && isset($_POST["dateofbirth"]) && isset($_POST["password"])) {

            $data = new stdClass();
            $data->type = 3;
            $data->firstName = $_POST["firstname"];
            $data->lastName = $_POST["lastname"];
            $data->email = $_POST["email"];
            if (!validDate($_POST["dateofbirth"])) {
                return false;
            }
            $data->dateOfBirth = DateTime::createFromFormat("j/n/Y", $_POST["dateofbirth"])->format("Y-m-d");
            $data->phone = $_POST["phone"];
            $data->password = $_POST["password"];

            return $data;
        }
        return false;
    }

    function checkStudentData() {
        $data = checkParentData();
        if ($data && isset($_POST["yearlevel"]) && isset($_POST["studentnumber"])) {
            $data->type = 1;
            $data->yearlevel = $_POST["yearlevel"];
            $data->studentNumber = $_POST["studentnumber"];
            $data->class = isset($_POST["class"]) ? $_POST["class"] : null;

            return $data;
        }
        return false;
    }

    function validDate($date) {
        $dt = DateTime::createFromFormat('j/n/Y', $date);
        return $dt && ($dt->format('j/n/Y') === $date
                    || $dt->format('d/m/Y') === $date
                    || $dt->format('j/m/Y') === $date
                    || $dt->format('d/n/Y') === $date);
    }

    // Validate that all the data is there
    $data = checkPostData();
    if ($data) {
        $data->confirmed = 0;
        $hash = password_hash($data->password, PASSWORD_DEFAULT);
        if ($hash) {
            $data->passwordHash = $hash;
            $newUserResult = User::createUser($data);
            if ($newUserResult->success) {
                $result->okay = true;
                $result->token = $newUserResult->token;
            } else {
                $result->message = "Unable to insert data into database.";
            }
        } else {
            $result->message = "Unable to generate a hash.";
        }
    } else {
        $result->message = "Invalid data.";
    }

    echo json_encode($result);

?>