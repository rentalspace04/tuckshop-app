<?php

    session_start();

    include_once $_SERVER['DOCUMENT_ROOT'] . "/lib/helper.php";

    if (Helper::isLoggedIn()) {
        Helper::logOut();
    }

    header("Location: /index.php");
?>