<?php
    include_once $_SERVER['DOCUMENT_ROOT'] . "/lib/token.php";
    include_once $_SERVER['DOCUMENT_ROOT'] . "/lib/user.php";

    header("Content-Type: application/json; charset=UTF-8");
    header("Access-Control-Allow-Origin: *");
    header('Access-Control-Allow-Methods: GET, POST, PATCH, PUT, DELETE, OPTIONS');
    header('Access-Control-Expose-Headers: x-json');
    header('Access-Control-Allow-Headers: x-prototype-version, x-requested-with, Origin, Content-Type');

    $jsonObj = new stdClass();
    $jsonObj->auth = false;

    // Check that all info is given
    function checkPostData() {
        return isset($_POST["email"]) && isset($_POST["password"]);
    }

    if (checkPostData()) {
        $email = $_POST["email"];
        $password = $_POST["password"];
        if (User::emailExists($email)) {
            $user = User::getByEmail($email);
            if ($user->passwordMatches($password)) {
                // Create the auth token (and 'use' any of user's existing
                // tokens)
                $newToken = Token::makeNewAppAuthToken($user);
                if ($newToken->success) {
                    $jsonObj->auth = true;
                    $jsonObj->userId = $user->userID;
                    $jsonObj->token = $newToken->token;
                }
            }
        }
    }

    echo json_encode($jsonObj);
?>
