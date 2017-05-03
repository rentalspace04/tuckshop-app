<?php

echo "started user";
include_once $_SERVER['DOCUMENT_ROOT'] . "/lib/helper.php";

echo "included helper";

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
    public function print() {
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

        return User::specialiseUser($user);
    }

    protected static function specialiseUser($baseUser) {
        // Fetch type specific info
        switch ($baseUser->type) {
            case User::$STUDENT:
                return User::getStudent($baseUser);
            case User::$CHILD:
                return User::getChild($baseUser);
            case User::$PARENT:
                return User::getParent($baseUser);
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
        return User::specialiseUser($user);
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

    // Make a new user object
    public static function newUser($id, $fname, $lname, $email, $dob, $phone, $type, $passwordHash, $confirmed = false) {
        $this->id = $id;
        $this->firstName = $fname;
        $this->lastName = $lname;
        $this->email = $email;
        $this->dateOfBirth = $dob;
        $this->phone = $phone;
        $this->passwordHash = $passwordHash;
        $this->type = $type;
        $this->confirmed = $confirmed;
    }

    public function typeString() {
        switch ($this->type) {
            case User::$STUDENT:
                return "Student";
            case User::$CHILD:
                return "Child";
            case User::$PARENT:
                return "Parent";
            default:
                return "User...";
        }
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

    function print() {
        parent::print();
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

    function print() {
        parent::print();
        echo $this->balance . "<br />";
        echo "Children: ";
        foreach ($this->children as $child) {
            echo "$child ";
        }
    }
}

?>