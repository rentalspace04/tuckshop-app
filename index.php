<?php

    session_start();

    include_once $_SERVER['DOCUMENT_ROOT'] . "/lib/helper.php";

    echo "included helper";

    if (Helper::isLoggedIn()) {
        Helper::redirect("/home.php");
    } else {
        Helper::redirect("/front.php");
    }

?>