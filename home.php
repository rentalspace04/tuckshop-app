<?php
    session_start();

    include_once $_SERVER['DOCUMENT_ROOT'] . "/lib/page.php";
    include_once $_SERVER['DOCUMENT_ROOT'] . "/lib/helper.php";

    // Check that we aren't logged in - if we are, redirect to home.php
    if (!Helper::isLoggedIn()) {
        Helper::redirect("/login.php");
    }

    // If there's an order in progress, delete it - they left the order page
    Helper::removeInProgressCart();

    $user = Helper::getLoggedInUser();

    // Setup that this is the Home page
    $page = new Page("Home Page", $user);

    include_once $_SERVER['DOCUMENT_ROOT'] . "/templates/main_start.php";

    // Generates the html for the set of task blocks for each user type
    function makeTasks($userType) {
        $output = "";
        switch ($userType) {
            case User::$STUDENT:
            case User::$CHILD:
                $output .= newOrderTaskBlock();
                $output .= orderHistoryTaskBlock();
                $output .= myAccountTaskBlock();
                break;
            case User::$PARENT:
                $output .= makeOrderForChildTaskBlock();
                $output .= manageChildrenTaskBlock();
                $output .= orderHistoryTaskBlock();
                $output .= myAccountTaskBlock();
                break;
            default:
                break;
        }
        return $output;
    }

    // Task generating functions
    function newOrderTaskBlock() {
        $output = "<a href=\"/order/\" class=\"task-block\">";
        $output .= "<h2>New Order</h2>";
        $output .= "<p>Hungry?</p>";
        $output .= "<p>Place a new order.</p>";
        $output .= "</a>";
        return $output;
    }

    function myAccountTaskBlock() {
        $output = "<a href=\"/account.php\" class=\"task-block\">";
        $output .= "<h2>My Account</h2>";
        $output .= "<p>Need to change your password?</p>";
        $output .= "<p>Want to update your email?</p>";
        $output .= "<p>Change your account details here.</p>";
        $output .= "</a>";
        return $output;
    }

    function orderHistoryTaskBlock() {
        $output = "<a href=\"/orderHistory.php\" class=\"task-block\">";
        $output .= "<h2>Order History</h2>";
        $output .= "<p>Wanna see whether your order is ready?</p>";
        $output .= "<p>Can't remember what you had for lunch yesterday?</p>";
        $output .= "<p>View your order history here.</p>";
        $output .= "</a>";
        return $output;
    }

    function manageChildrenTaskBlock() {
        return "";
    }

    function makeOrderForChildTaskBlock() {
        return newOrderTaskBlock();
    }

?>

<h1>My Tasks</h1>

<div id="homepage-content">
    <div id="homepage-sidebar">
        <?php
            echo "<p>$user->firstName <b>$user->lastName</b></p>";
            if ($user->type != User::$STUDENT) {
                echo "<p>BALANCE</p>";
            }
        ?>
        <p>Date of Last Order</p>
        <p><a href="/account.php">My Account &gt;&gt;</a></p>
    </div>
    <div id="homepage-tasks">

        <?php
            echo makeTasks($user->type);
        ?>

    </div>
</div>

<?php
    include_once $_SERVER['DOCUMENT_ROOT'] . "/templates/main_end.php"
?>