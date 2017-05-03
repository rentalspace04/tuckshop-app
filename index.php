<?php

    session_start();

    include_once $_SERVER['DOCUMENT_ROOT'] . "/lib/helper.php";

    if (Helper::isLoggedIn()) {
        Helper::redirect("/home.php");
    } else {
        Helper::redirect("/front.php");
    }

?>