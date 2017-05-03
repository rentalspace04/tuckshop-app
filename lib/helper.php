<?php

    echo "started helper";

    echo $_SERVER['DOCUMENT_ROOT'] . "<br />";

    include_once $_SERVER['DOCUMENT_ROOT'] . "\\lib\\user.php";

    echo "included user";

    class Helper {
        public static function redirect($location, $code = 301) {
            header("Location: $location", true, $code);
            exit();
        }

        public static function tuckshopPDO() {
            $host = '127.0.0.1';
            $db   = 'tuckshop_data';
            $user = 'root';
            $pass = '';
            $charset = 'utf8';

            $dsn = "mysql:host=$host;dbname=$db;charset=$charset";
            $opt = [
                PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                PDO::ATTR_EMULATE_PREPARES   => false,
            ];
            $pdo = new PDO($dsn, $user, $pass, $opt) or die("Problem trying to open database");

            return $pdo;
        }

        // Checks if there's currently a logged in session
        public static function isLoggedIn() {
            if (!isset($_SESSION["auth"]) || !isset($_SESSION["userID"])) {
                // If auth or user aren't set, they can't be logged in
                return false;
            }
            // Return whether they're logged in
            return $_SESSION["auth"];
        }

        public static function logIn($userID) {
            $_SESSION["auth"] = true;
            $_SESSION["userID"] = $userID;
        }

        public static function logOut() {
            session_unset();
        }

        public static function getLoggedInUser() {
            if (Helper::isLoggedIn()) {
                $id = $_SESSION["userID"];
                return User::getById($id);
            } else {
                return NULL;
            }
        }

        public static function removeInProgressCart() {
            if (isset($_SESSION["cart"])) {
                unset($_SESSION["cart"]);
            }
        }
    }

?>