<?php
    session_start();

    include_once $_SERVER['DOCUMENT_ROOT'] . "/lib/page.php";
    include_once $_SERVER['DOCUMENT_ROOT'] . "/lib/helper.php";
    include_once $_SERVER['DOCUMENT_ROOT'] . "/lib/user.php";

    // Check that we aren't logged in - if we are, redirect to home.php
    if (Helper::isLoggedIn()) {
        Helper::redirect("/home.php");
    }

    // Setup that this is the Home page
    $page = new Page("Create Account", NULL);

    // $page->addScript("/js/class.js");
    $page->addScript("/js/createAccount.js");

    function getFormTable($isParent) {
        $out = "";

        $out .= "<p class=\"errorMessage centered\"></p>";

        $out .= "<table class=\"form-table\"><tbody>";

        $out .= addText($isParent, true, "First Name");
        $out .= addText($isParent, true, "Last Name");
        $out .= addText($isParent, true, "Email");
        $out .= addText($isParent, true, "Phone");
        $out .= addText($isParent, true, "dateofbirth", "Date Of Birth", "dd/mm/yyyy");

        if (!$isParent) {
            $out .= addText($isParent, true, "Year Level");
            $out .= addText($isParent, true, "Student Number");
            $out .= addText($isParent, false, "Class");
        }

        $out .= addPassword($isParent, true, "Password");
        $out .= addPassword($isParent, true, "confirmpassword", "Confirm Password", "Password");

        $hiddenValue = $isParent ? "parent" : "student";

        $out .= "</tbody></table>";

        $out .= "<input type=\"hidden\" name=\"userType\" value=\"$hiddenValue\" />";

        $out .= "<p class=\"centered\"><input type=\"submit\" class=\"button\" value=\"Create Account\" /></p>";

        return $out;
    }

    function addText($isParent, $required, $name, $label = null, $placeholder = null) {
        return addInput("text", $required, $isParent, $name, $label, $placeholder);
    }

    function addPassword($isParent, $required, $name, $label = null, $placeholder = null) {
        return addInput("password", $required, $isParent, $name, $label, $placeholder);
    }

    function addInput($type, $required, $isParent, $name, $label = null, $placeholder = null) {
        $out = "";

        $idPrefix = $isParent ? "parent" : "student";
        $requiredClass = $required ? "required" : "";

        if ($label == null) {
            $label = $name;
        }

        if ($placeholder == null) {
            $placeholder = $name;
        }

        $name = str_replace(" ", "", strtolower($name));

        $out .= "<tr><td><label for=\"$name\" class=\"$requiredClass\">$label</td>";
        $out .= "<td><input type=\"$type\" class=\"textbox $requiredClass\" id=\"create-$idPrefix-$name\" name=\"$name\" placeholder=\"$placeholder\" /></tr>";

        return $out;
    }

    include_once $_SERVER['DOCUMENT_ROOT'] . "/templates/dialog_start.php";

?>
<h2 class="formQuestion">Are you a student or a parent?</h2>
<ul id="userTypeSelect" class="tab-select">
    <li class="selected" data-form-id="createAccountStudent">Student</li>
    <li data-form-id="createAccountParent">Parent</li>
</ul>
<div id="createAccountFormContainer">
    <form id="createAccountStudent" class="create-account-form">
        <?php echo getFormTable(false); ?>
    </form>

    <form id="createAccountParent" class="create-account-form">
        <?php echo getFormTable(true); ?>
    </form>
</div>


<?php

    include_once $_SERVER['DOCUMENT_ROOT'] . "/templates/dialog_end.php";
?>