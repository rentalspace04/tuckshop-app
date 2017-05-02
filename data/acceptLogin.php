<?php
    /*
        Takes a password and email in the POST,
        Returns a JSON object telling app whether user login is successful
    */

    session_start();

    include_once $_SERVER['DOCUMENT_ROOT'] . "/lib/helper.php";

    header("Content-Type: application/json; charset=UTF-8");
    $jsonObj = new stdClass();

    // If user is already logged in, just tell them they've logged in
    if (Helper::isLoggedIn()) {
        $jsonObj->auth = true;
        //$jsonObj->step = "already logged in";
        echo json_encode($jsonObj);
        return; // We're done
    }

    include_once $_SERVER['DOCUMENT_ROOT'] . "/lib/user.php";

    // Work out if the right params were passed
    if (!isset($_POST["email"]) || !isset($_POST["password"])) {
        $jsonObj->auth = false;
        //$jsonObj->step = "bad args";
        echo json_encode($jsonObj);
        return; // We're done
    }

    // Check if there's a user with that email
    $email = $_POST["email"];
    $pwd = $_POST["password"];
    if (User::emailExists($email)) {
        // Get the user with that email
        $user = User::getByEmail($email);
        // Check if the password given is correct
        $match = password_verify($pwd, $user->passwordHash);
        if ($match) {
            // Check that the user is confirmed
            if ($user->confirmed) {
                Helper::logIn($user->userID);
                $jsonObj->auth = true;
                //$jsonObj->step = "logged in";
                echo json_encode($jsonObj);
                return; // We're done
            } else {
                //$jsonObj->step = "not confirmed";
            }
        } else {
            //$jsonObj->step = "bad password";
        }
    } else {
        //$jsonObj->step = "bad email";
    }

    $jsonObj->auth = false;
    echo json_encode($jsonObj);
    return; // We're done

    //TODO Store login attempts in session
?>