<?php

    include_once $_SERVER['DOCUMENT_ROOT'] . "/lib/user.php";

    class Update {

        // Updates a parent user's balance
        public static function balance($pdo, $user, $balance) {
            if ($user->type != User::$PARENT) {
                return false;
            }

            $query = "UPDATE Parents SET balance=? WHERE userID=?";
            $statement = $pdo->prepare($query);
            $statement->execute([$balance, $user->userID]);

            // Returns the number of rows effected
            return $statement->rowCount();
        }

        // Updates a child user's allowance
        public static function allowance($pdo, $user, $allowance) {
            if ($user->type != User::$CHILD) {
                return false;
            }

            $query = "UPDATE Children SET allowance=? WHERE childID=?";

            $statement = $pdo->prepare($query);
            $statement->execute([$allowance, $user->userID]);

            // Returns the number of rows effected
            return $statement->rowCount();
        }
    }
?>