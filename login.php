<?php

    session_start();

    include_once $_SERVER['DOCUMENT_ROOT'] . "/lib/helper.php";

    if (Helper::isLoggedIn()) {
        Helper::redirect("/home.php");
    }

    include_once $_SERVER['DOCUMENT_ROOT'] . "/lib/page.php";

    $page = new Page("Log In", NULL);
    $page->addScript("/js/login.js");

    $redirectAction = isset($_GET["redirect"]) && $_GET["redirect"] ? $_GET["redirect"] : "/home.php";

    include $_SERVER['DOCUMENT_ROOT'] . "/templates/dialog_start.php";

?>
        <form id="login-form" <?php echo "action=\"$redirectAction\"" ?>>
            <div id="formErrorMessage"></div>
            <table>
                <tbody>
                    <tr>
                        <td><label for="email">Email:</label></td>
                        <td>
                            <input type="text" class="textbox" name="email" id="email" placeholder="Email" />
                        </td>
                    </tr>
                    <tr>
                        <td><label for="password">Password:</label></td>
                        <td>
                            <input type="password" class="textbox" name="password" id="password" placeholder="Password" />
                        </td>
                    </tr>
                </tbody>
            </table>
            <input type="submit" value="Log In" class="button" />
        </form>

<?php
    include $_SERVER['DOCUMENT_ROOT'] . "/templates/dialog_end.php";
?>