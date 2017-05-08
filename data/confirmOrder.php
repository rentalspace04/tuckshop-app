<?php

    session_start();

    include_once $_SERVER['DOCUMENT_ROOT'] . "/lib/helper.php";
    include_once $_SERVER['DOCUMENT_ROOT'] . "/lib/cart.php";
    include_once $_SERVER['DOCUMENT_ROOT'] . "/lib/update.php";

    header("Content-Type: application/json; charset=UTF-8");

    // If any errors occur, assume it hasn't been submitted successfully
    function handle_error($errno, $errstr) {
        $json = new stdClass();
        $json->success = false;
        $json->message = "[$errno] - $errstr";
        $json->serverError = true;

        echo json_encode($json);
        exit();
    }

    set_error_handler("handle_error");

    function checkPost($user) {
        $result = true;
        // Check that payment type and pickup time are set
        if (isset($_POST["paymentType"]) && isset($_POST["pickupTime"])) {
            // check that the pickup time is valid
            $result = $result & Helper::isOrderTime($_POST["pickupTime"]);
            // if the user's a parent, check that they're ordering for one of
            // their children
            if ($user->type == User::$PARENT) {
                $result = $result && isset($_POST["orderFor"]);
                if ($result) {
                    // only check if they gave an id for their child
                    $result = $result && in_array($_POST["orderFor"], $user->children);
                }
            }
        }
        return $result;
    }

    function processPayment($pdo, $user, $cart, $type, $forUser = null) {
        $result = new stdClass();
        $result->success = false;
        $result->message = "";
        $result->serverError = false;
        switch (strtolower($type)) {
            // These are handled externally
            case "paypal":
            case "credit":
            case "cash":
                $result->success = true;
                break;
            case "balance":
                $result = handleBalancePayment($pdo, $user, $cart);
                break;
            case "allowance":
                $result = handleAllowancePayment($pdo, $user, $cart, $forUser);
                break;
            default:
                $result->message = "Invalid payment type of '$type'";
        }
        return $result;
    }

    // Handles a payment made using a parent user's pre-paid balance
    function handleBalancePayment($pdo, $user, $cart) {
        $result = new stdClass();
        $result->success = false;
        $result->message = "";
        $result->serverError = true;

        $cost = $cart->totalCost();

        // Check that user enough balance
        if (isset($user->balance) && $user->balance >= $cost) {
            // Work out user's new balance
            $newBalance = $user->balance - $cost;
            // Update the user's balance in db
            if (Update::balance($pdo, $user, $newBalance)) {
                $result->success = true;
                $result->serverError = false;
            } else {
                // It fails....
                $result->message = "Failed to update balance in db";
            }

        } else {
            $result->message = "Your balance isn't enough to pay for order";
            $result->serverError = false;
        }

        return $result;
    }

    // Handles a payment made with the allowance of a student user (either the
    // student placing the order, or the student for whom the order was made)
    function handleAllowancePayment($pdo, $user, $cart, $forUser) {
        $result = new stdClass();
        $result->success = false;
        $result->message = "";
        $result->serverError = true;

        // The payee is either the child (if the one ordering is a parent) or
        // the user making an order (if the one ordering is a student)
        $payee = $user->type == User::$PARENT ? $forUser : $user;

        $cost = $cart->totalCost();

        if (isset($payee->allowance) && $payee->allowance >= $cost) {
            // Get user's new allowance
            $newAllowance = $payee->allowance - $cost;
            // Update payee's allowance
            if (Update::allowance($pdo, $payee, $newAllowance)) {
                $result->success = true;
                $result->serverError = false;
            } else {
                // It fails....
                $result->message = "Failed to update allowance in db";
            }
        } else {
            $name = $payee == $user ? "Your child's" : "Your";
            $result->message = "$name allowance is not enough to pay for this order";
            $result->serverError = false;
        }

        return $result;
    }

    $json = new stdClass();
    $json->success = false;
    $json->serverError = true;
    $json->message = "";

    // User should be logged in and have a cart in session
    if (Helper::isLoggedIn() && isset($_SESSION["cart"])) {
        $user = Helper::getLoggedInUser();
        // Do some basic smoke checking of post data
        if (checkPost($user)) {
            $cart = unserialize($_SESSION["cart"]);
            // Cart should be valid
            if ($cart->isValid()) {
                // If the user is a parent, they'll be ordering for their child
                // while if the user is a child/student, they're ordering
                // for themselves
                $forUser = $user->type == User::$PARENT ? User::getById($_POST["orderFor"]) : $user;

                // Start a PDO transaction so that it can be rolled back
                // if something goes wrong...
                $pdo = Helper::tuckshopPDO();
                $pdo->beginTransaction();

                // Try to process the payment
                $paymentResult = processPayment($pdo, $user, $cart, $_POST["paymentType"], $forUser);

                // Submit the order iff payment was good
                if ($paymentResult->success) {
                    if ($cart->submitAsNewOrder($pdo, $user, $forUser, $_POST["pickupTime"])) {
                        $json->success = true;
                        $json->serverError = false;
                        $pdo->commit(); // It succeeded, so save db changes
                    } else {
                        $json->message = "Unable to submit order";
                        $pdo->rollback(); // It failed, so rollback db changes
                    }
                } else {
                    $json->message = $paymentResult->message;
                    $json->serverError = $paymentResult->serverError;
                    $pdo->rollback(); // It failed, so rollback db changes
                }
            } else {
                $json->message = "Invalid cart";
            }
        } else {
            $json->message = "No cart in session";
        }
    } else {
        $json->message = "You aren't logged in";
    }

    // Print out the result as json
    echo json_encode($json);
?>