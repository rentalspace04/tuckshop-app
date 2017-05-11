<?php
    session_start();

    include_once $_SERVER['DOCUMENT_ROOT'] . "/lib/page.php";
    include_once $_SERVER['DOCUMENT_ROOT'] . "/lib/helper.php";
    include_once $_SERVER['DOCUMENT_ROOT'] . "/lib/token.php";

    // Check that we aren't logged in - if we are, redirect to home.php
    if (Helper::isLoggedIn()) {
        Helper::redirect("/home.php");
    }

    function showConfirmed() {
        $out = "";

        $out .= "<h3 class=\"centered\">Your account is now verified. Now you can:</h3>";
        $out .= "<p class=\"centered\"><a class=\"button\" href=\"/login.php\">Log In</a></p>";

        return $out;
    }

    function showNotConfirmed() {
        $out = "";

        $out .= "<p class=\"centered\">This ticket is either expired, already used or invalid.</p>";
        $out .= "<p class=\"centered warningMessage\">Your Account has not been verified.</p>";

        return $out;
    }

    // Setup that this is the Home page
    $page = new Page("Verify Account", NULL);

    $confirmed = false;

    if (isset($_GET["token"])) {
        $token = urldecode($_GET["token"]);
        $confirmed = Token::consumeToken($token, Token::CONFIRM_ACCOUNT);
    }

    include_once $_SERVER['DOCUMENT_ROOT'] . "/templates/main_start.php";

    if ($confirmed) {
        echo showConfirmed();
    } else {
        echo showNotConfirmed();
    }

    include_once $_SERVER['DOCUMENT_ROOT'] . "/templates/main_end.php";
?>