<?php

include_once $_SERVER['DOCUMENT_ROOT'] . "/lib/helper.php";

?>

<div id="main-menu-box">
    <a href="/" id="main-menu-logo">Tuckr</a>
    <div id="main-menu-options">
        <?php
        if (Helper::isLoggedIn()) {
            echo $page->getMenuItems();
        } else {
            echo '<a href="template.html" id="main-menu-account">Create<br />Account</a>'."\n";
            echo '<a href="/login.php" id="main-menu-login">Log<br />In</a>'."\n";
        }
        ?>
    </div>
</div>