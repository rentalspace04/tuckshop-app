<?php

    include_once $_SERVER['DOCUMENT_ROOT'] . "/lib/helper.php";
    include_once $_SERVER['DOCUMENT_ROOT'] . "/lib/token.php";

    class User {
        // User types
        static $STUDENT = 1;
        static $CHILD = 2;
        static $PARENT = 3;

        // Fields
        public $userID;
        public $firstName;
        public $lastName;
        public $email;
        public $dateOfBirth;
        public $phone;
        public $passwordHash;
        public $type; // 1 = student, 2 = child of parent, 3 = parent
        public $confirmed;

        public function getId() {
            return $this->userID;
        }
        public function getFirstName() {
            return $this->firstName;
        }
        public function getLastName() {
            return $this->lastName;
        }
        public function getEmail() {
            return $this->email;
        }
        public function getDob() {
            return $this->dateOfBirth;
        }
        public function getPhone() {
            return $this->phone;
        }
        public function getPasswordHash() {
            return $this->passwordHash;
        }
        public function passwordMatches($password) {
            return password_verify($password, $this->passwordHash);
        }
        public function printUser() {
            echo $this->userID . "<br />";
            echo $this->typeString() . "<br />";
            echo "$this->firstName $this->lastName<br />";
            echo $this->email . "<br />";
            echo $this->dateOfBirth . "<br />";
            echo $this->phone . "<br />";
            echo $this->passwordHash . "<br />";
            echo ($this->confirmed ? "C" : "Unc") . "onfirmed<br />";
        }

        public static function getByID($id) {
            // Prepare the query and PDO
            $pdo = Helper::tuckshopPDO();
            $query = "SELECT * FROM Users WHERE userID=?";
            $statement = $pdo->prepare($query);

            $statement->execute([$id]);
            $user = $statement->fetchAll(PDO::FETCH_CLASS, 'User');

            // ID is unique, so there should only be one row
            $user = $user[0];

            return self::specialiseUser($user);
        }

        protected static function specialiseUser($baseUser) {
            // Fetch type specific info
            switch ($baseUser->type) {
                case self::$STUDENT:
                    return self::getStudent($baseUser);
                case self::$CHILD:
                    return self::getChild($baseUser);
                case self::$PARENT:
                    return self::getParent($baseUser);
                default:
                    return $baseUser;
            }
        }

        protected static function getStudent($baseUser) {
            // Prepare the query and PDO
            $pdo = Helper::tuckshopPDO();
            $query = "SELECT Users.userID, firstName, lastName, dateOfBirth, "
                    ."phone, type, email, passwordHash, confirmed, grade, "
                    ."studentNumber, class FROM Users INNER JOIN Students "
                    ."ON Users.userID=Students.userID WHERE Users.userID=?";
            $statement = $pdo->prepare($query);

            $statement->execute([$baseUser->userID]);
            $user = $statement->fetchAll(PDO::FETCH_CLASS, 'StudentUser');

            // ID is unique, so there should only be one row
            $user = $user[0];
            return $user;
        }

        protected static function getChild($baseUser) {
            // Prepare the query and PDO
            $pdo = Helper::tuckshopPDO();
            $query = "SELECT Users.userID, firstName, lastName, dateOfBirth, "
                    ."phone, type, email, passwordHash, confirmed, grade, "
                    ."studentNumber, class, parentID, allowance FROM Users "
                    ."INNER JOIN Students ON Users.userID=Students.userID "
                    ."INNER JOIN Children ON Users.userID=Children.childID "
                    ."WHERE Users.userID=?";
            $statement = $pdo->prepare($query);

            $statement->execute([$baseUser->userID]);
            $user = $statement->fetchAll(PDO::FETCH_CLASS, 'ChildUser');

            // ID is unique, so there should only be one row
            $user = $user[0];

            return $user;
        }

        protected static function getParent($baseUser) {
            // Prepare the query and PDO
            $pdo = Helper::tuckshopPDO();
            $query = "SELECT Users.userID, firstName, lastName, dateOfBirth, "
                    ."phone, type, email, passwordHash, confirmed, balance FROM "
                    ."Users INNER JOIN Parents ON Users.userID=Parents.userID "
                    ."WHERE Users.userID=?";
            $statement = $pdo->prepare($query);

            $statement->execute([$baseUser->userID]);
            $user = $statement->fetchAll(PDO::FETCH_CLASS, 'ParentUser');

            // ID is unique, so there should only be one row
            $user = $user[0];

            // Get all the parent's children's IDs
            $query = "SELECT childID FROM Children WHERE parentID=?";

            $statement = $pdo->prepare($query);

            $statement->execute([$baseUser->userID]);
            $childIDs = $statement->fetchAll(PDO::FETCH_COLUMN);

            $user->addChildren($childIDs);

            return $user;
        }

        public static function getByEmail($email) {
            // Prepare the query and PDO
            $pdo = Helper::tuckshopPDO();
            $query = "SELECT * FROM Users WHERE email=?";
            $statement = $pdo->prepare($query);

            $statement->execute([$email]);

            $user = $statement->fetchAll(PDO::FETCH_CLASS, 'User');

            // email is unique, so there should only be one row
            $user = $user[0];

            // Get all the info for that user's user type
            return self::specialiseUser($user);
        }

        public static function idExists($id) {
            // Prepare the query and PDO
            $pdo = Helper::tuckshopPDO();
            $query = "SELECT COUNT(1) FROM Users WHERE userID=?";
            $statement = $pdo->prepare($query);

            $statement->execute([$id]);

            // we're doing a count, so one row/column
            $count = $statement->fetchColumn();

            return $count > 0;
        }

        public static function emailExists($email) {
            // Prepare the query and PDO
            $pdo = Helper::tuckshopPDO();
            $query = "SELECT Count(1) FROM Users WHERE email=?";
            $statement = $pdo->prepare($query);

            $statement->execute([$email]);

            // we're doing a count, so one row/column
            $count = $statement->fetchColumn();

            return $count > 0;
        }

        // Tries to insert a new user into the database and returns an object
        // containing the new user's id and the verification token if it is
        // successful. If not, makes no changes to the db and returns false
        public static function createUser($user) {
            $result = new stdClass();
            $result->success = false;
            $result->token = "";
            $result->userID = -1;

            $pdo = Helper::tuckshopPDO();
            $pdo->beginTransaction();

            $query  = "INSERT INTO Users (firstName, lastName, dateOfBirth, phone, type, email, passwordHash, confirmed) VALUES (:fn, :ln, :dob, :ph, :type, :email, :pwd, :con)";

            $params = [
                "fn" => $user->firstName,
                "ln" => $user->lastName,
                "dob" => $user->dateOfBirth,
                "ph" => $user->phone,
                "type" => $user->type,
                "email" => $user->email,
                "pwd" => $user->passwordHash,
                "con" => $user->confirmed
            ];

            $statement = $pdo->prepare($query);
            $statement->execute($params);

            // Only extend the user if it was inserted successfully
            $inserted = $statement->rowCount();
            if ($inserted > 0) {
                $user->userID = $pdo->lastInsertId();
                $result->userID = $user->userID;

                $extendResult = false;

                // Now "extend" the user - make them a student/parent/child
                if ($user->type == self::$STUDENT) {
                    $extendResult = self::createStudent($pdo, $user);
                } else if ($user->type == self::$PARENT) {
                    $extendResult = self::createParent($pdo, $user);
                } else {
                    $extendResult = self::createChild($pdo, $user);
                }

                // Only continue if extending the user worked
                if ($extendResult) {
                    // Make a verification token for the user
                    $token = Token::makeVerificationToken($pdo, $user);
                    if ($token->success) {
                        $result->token = $token->token;
                        $result->success = true;
                        // Save changes to the db
                        $pdo->commit();
                        return $result;
                    }
                }
            }
            // rollback if it didn't
            $pdo->rollback();
            return $result;
        }

        private static function createStudent($pdo, $user) {
            $query  = "INSERT INTO Students (userID, grade, studentNumber, class) VALUES (:uid, :g, :sn, :c)";

            $params = [
                "uid" => $user->userID,
                "g" => $user->yearlevel,
                "sn" => $user->studentNumber,
                "c" => $user->class
            ];

            $statement = $pdo->prepare($query);
            $statement->execute($params);

            return $statement->rowCount() > 0;
        }

        private static function createParent($pdo, $user) {
            $query  = "INSERT INTO Parents (userID, balance) VALUES (:uid, :bal)";

            $params = [
                "uid" => $user->userID,
                "bal" => 0
            ];

            $statement = $pdo->prepare($query);
            $statement->execute($params);

            return $statement->rowCount() > 0;
        }

        private static function createChild($pdo, $user) {
            $studentResult = self::createStudent($pdo, $user);
            if (!$studentResult) {
                return false;
            }

            $query  = "INSERT INTO Children (userID, parentID, allowance) VALUES (:uid, :pid, :all)";

            $params = [
                "uid" => $user->userID,
                "pid" => $user->parentID,
                "all" => 0
            ];

            $statement = $pdo->prepare($query);
            $statement->execute($params);

            return $statement->rowCount() > 0;
        }

        public function typeString() {
            switch ($this->type) {
                case self::$STUDENT:
                    return "Student";
                case self::$CHILD:
                    return "Child";
                case self::$PARENT:
                    return "Parent";
                default:
                    return "User...";
            }
        }

        public static function isConfirmed($userID) {
            if (self::idExists($userID)) {
                $user = self::getByID($userID);
                return $user->confirmed;
            }
            return false;
        }

        public static function isChild($parentID, $childID) {
            $pdo = Helper::tuckshopPDO();
            $query = "SELECT COUNT(*) FROM Children WHERE childID=? AND parentID=?";
            $statement = $pdo->prepare($query);

            $statement->execute([$childID, $parentID]);
            $matches = $statement->fetchColumn();

            return $matches;
        }

    }

    class StudentUser extends User {
        public $grade;
        public $studentNumber;
        public $class;
    }

    class ChildUser extends StudentUser {
        public $allowance;
        public $parentID;

        function printUser() {
            parent::printUser();
            echo "Child of parent($this->parentID)<br />";
        }
    }

    class ParentUser extends User {
        public $balance;
        public $children = array();

        function addChildren($children) {
            foreach ($children as $child) {
                $this->children[] = $child;
            }
        }
        function addChild($child) {
            $this->children[] = $child;
        }

        function printUser() {
            parent::printUser();
            echo $this->balance . "<br />";
            echo "Children: ";
            foreach ($this->children as $child) {
                echo "$child ";
            }
        }
    }

?>