<?php

    echo "started file";

    session_start();

    echo "started session";

    include_once $_SERVER['DOCUMENT_ROOT'] . "/lib/helper.php";

    echo "included";

    if (Helper::isLoggedIn()) {
        Helper::redirect("/home.php");
    } else {
        Helper::redirect("/front.php");
    }

?>