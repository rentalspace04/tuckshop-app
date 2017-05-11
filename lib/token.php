<?php
    include_once $_SERVER['DOCUMENT_ROOT'] . "/lib/helper.php";
    include_once $_SERVER['DOCUMENT_ROOT'] . "/lib/user.php";

    class Token {
        public $forUser;
        public $token;
        public $tokenType;
        public $used;
        public $timestamp;

        // Types of token
        const CONFIRM_ACCOUNT = 1;

        private static function tokenExists($token, $pdo = null) {
            if ($pdo == null) {
                $pdo = Helper::tuckshopPDO();
            }
            $query  = "SELECT COUNT(*) FROM Tokens WHERE token = ?";

            $statement = $pdo->prepare($query);
            $statement->execute([$token]);

            return $statement->fetchColumn() > 0;
        }

        public static function getToken($token) {
            $pdo = Helper::tuckshopPDO();

            $query  = "SELECT * FROM Tokens WHERE token = ?";

            $statement = $pdo->prepare($query);
            $statement->execute([$token]);

            $tokens = $statement->fetchAll(PDO::FETCH_CLASS, "Token");

            return $tokens[0];
        }

        public static function makeVerificationToken($pdo, $user, $type = self::CONFIRM_ACCOUNT) {
            $result = new stdClass();
            $result->success = false;
            $result->token = "";

            // Make sure that the token is unique
            do {
                $token = self::makeToken();
            } while (self::tokenExists($token, $pdo));

            $result->token = $token;

            $query  = "INSERT INTO Tokens (forUser, token, tokenType, used) VALUES (:uid, :token, :ttype, :used)";

            $params = [
                "uid" => $user->userID,
                "token" => $token,
                "ttype" => $type,
                "used" => false
            ];

            $statement = $pdo->prepare($query);
            $statement->execute($params);

            if ($statement->rowCount() > 0) {
                $result->success = true;
            }
            return $result;
        }

        // Makes a 40 char pseudo random token (30 bytes encoded in Base64)
        public static function makeToken() {
            return base64_encode(openssl_random_pseudo_bytes(30));
        }

        // Attempts to "uses up" a token. Returns whether or not a token was
        // used up (true), or the token doesn't exist/db error/something goes
        // wrong (false)
        public static function consumeToken($token, $type = null) {
            // Only consume the token if it exists
            if (self::tokenExists($token)) {
                $tokenObj = self::getToken($token);
                // Check that the token hasn't already been used
                if ($tokenObj->used) {
                    return false;
                }

                // If no token type was given, switch through all and try to
                // do the type-specific action to consume the token
                if ($type == null) {
                    return self::consumeType($tokenObj);
                } else {
                    // Only do the action if the token's type matches the given
                    // token type
                    if ($type == $tokenObj->tokenType) {
                        return self::consumeType($tokenObj);
                    }
                }
            }
            return false;
        }

        // Consumes the token in the way its type should be consumed
        // Returns whether the consume action succeeded
        private static function consumeType($tokenObj) {
            switch ($tokenObj->tokenType) {
                case self::CONFIRM_ACCOUNT:
                    // Try to verify the user - return whether or not
                    // it worked
                    return self::confirmUser($tokenObj);
                default:
                    return false;
            }
        }

        private static function confirmUser($tokenObj) {
            // Only try to confirm the user if they exist and aren't confirmed
            if (User::idExists($tokenObj->forUser) && !User::isConfirmed($tokenObj->forUser)) {
                $pdo = Helper::tuckshopPDO();

                $worked = true; // Record whether or not changes go through

                // Start a transaction so changes only stick after completion
                $pdo->beginTransaction();
                $query  = "UPDATE Tokens SET used = 1 WHERE token = ?";
                $statement = $pdo->prepare($query);
                $statement->execute([$tokenObj->token]);
                // Record whether or not changes go through
                $worked = $worked && ($statement->rowCount() > 0);

                $query  = "UPDATE Users SET confirmed = 1 WHERE userID = ?";
                $statement = $pdo->prepare($query);
                $statement->execute([$tokenObj->forUser]);
                // Record whether or not changes go through
                $worked = $worked && ($statement->rowCount() > 0);

                // Check if they worked
                if ($worked) {
                    $pdo->commit();
                    return true;
                } else {
                    $pdo->rollback();
                }
            }
            return false;
        }

    }
?>