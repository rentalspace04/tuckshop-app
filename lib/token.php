<?php
    include_once $_SERVER['DOCUMENT_ROOT'] . "/lib/helper.php";
    include_once $_SERVER['DOCUMENT_ROOT'] . "/lib/user.php";

    class Token {
        public $forUser;
        public $token;
        public $tokenType;
        public $used;
        public $timestamp;

        const AUTH_EXPIRY = 28;

        // Types of token
        const CONFIRM_ACCOUNT = 1;
        const APP_AUTH = 2;

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

        public static function checkAppAuthToken($userID, $token) {
            // Check that the token is valid - it exists, is for the user and
            // isn't expired
            if (self::tokenExists($token)) {
                $tokenObj = self::getToken($token);
                $expired = self::checkAuthExpired($tokenObj);
                // Return whether or not the given token is valid -
                // that is, if it's not expired, it's for the given user and
                // it hasn't been used
                return !$expired && $tokenObj->forUser == $userID
                    && !$tokenObj->used;
            }
        }

        // Makes a new APP_AUTH token for the given user, and makes sure that
        // existing tokens are used up for them.
        // Returns an object with a success and token field, indicating whether
        // or not the operation worked (a token was inserted into the DB), and
        // the actual token that was inserted
        public static function makeNewAppAuthToken($user) {
            $pdo = Helper::tuckshopPDO();
            // Doesn't really matter if it worked - there may not even be
            // old tokens to use
            self::useOldAppTokens($pdo, $user);
            $result = self::makeVerificationToken($pdo, $user, self::APP_AUTH);
            return $result;
        }

        // 'Uses up' any APP_AUTH tokens for the given user
        // Returns the number of rows affected
        private static function useOldAppTokens($pdo, $user) {
            $query = "UPDATE Tokens SET used = 1 WHERE forUser = ? AND tokenType = ?";
            $statement = $pdo->prepare($query);
            $statement->execute([$user->userID, self::APP_AUTH]);
            return $statement->rowCount();
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
                case self::APP_AUTH:
                    return self::checkAuthExpired($tokenObj);
                default:
                    return false;
            }
        }

        private static function confirmUser($tokenObj) {
            // Only try to confirm the user if they exist and aren't confirmed
            if (User::idExists($tokenObj->forUser) && !User::isConfirmed($tokenObj->forUser)) {
                $pdo = Helper::tuckshopPDO();

                if (self::setTokenUsed($tokenObj)) {
                    // Start a transaction so changes only stick after completion
                    $pdo->beginTransaction();
                    $query  = "UPDATE Users SET confirmed = 1 WHERE userID = ?";
                    $statement = $pdo->prepare($query);
                    $statement->execute([$tokenObj->forUser]);
                    // Record whether or not changes go through
                    $worked = ($statement->rowCount() > 0);

                    // Check if they worked
                    if ($worked) {
                        $pdo->commit();
                        return true;
                    } else {
                        $pdo->rollback();
                    }
                }
            }
            return false;
        }

        private static function setTokenUsed($tokenObj) {
            // Start a transaction so changes only stick after completion
            $pdo->beginTransaction();
            $query  = "UPDATE Tokens SET used = 1 WHERE token = ?";
            $statement = $pdo->prepare($query);
            $statement->execute([$tokenObj->token]);

            // Record whether or not changes go through
            $worked = $statement->rowCount() > 0;
            if ($worked) {
                $pdo->commit();
            } else {
                $pdo->rollback();
            }
            return $worked;
        }

        // Checks if an app auth token is still valid. If not, it consumes
        // it - that means that if it returns true, the token had expired
        // and the user should not be treated as authenticated
        private static function checkAuthExpired($tokenObj) {
            $pdo = Helper::tuckshopPDO();
            // Check if it's still valid
            $query = "SELECT COUNT(*) FROM Tokens WHERE token = ? AND NOW() < DATE_ADD(timestamp, INTERVAL ? DAY)";
            $statement = $pdo->prepare($query);
            $statement->execute([$tokenObj->token, self::AUTH_EXPIRY]);

            $stillValid = $statement->fetchColumn();

            if (!$stillValid) {
                // Don't worry too much if it's marked as used - it's expired
                // anyway, so people won't be able to use it
                self::setTokenUsed($tokenObj);
            }

            // Invert it, so that tells whether or not it was consumed
            return !$stillValid;
        }

    }
?>