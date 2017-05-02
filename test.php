<?php

include_once $_SERVER['DOCUMENT_ROOT'] . "/lib/user.php";
include_once $_SERVER['DOCUMENT_ROOT'] . "/lib/items.php";
include_once $_SERVER['DOCUMENT_ROOT'] . "/lib/cart.php";
include_once $_SERVER['DOCUMENT_ROOT'] . "/lib/category.php";

/*if (User::emailExists("danyon.ramay@yahoo.com.au")) {
    echo "d.r@y.c.a exists..!.!.!";
} else {
    echo "d.r@y.c.a doesn't exist....";
}

$email = "charliematthaei@teleworm.us";

if (User::emailExists($email)) {
    $user = User::getByEmail($email);
    $user->print();
} else {
    echo "charlie matthaei doesn't exist....";
}

echo "<br/>";

$hash1 = password_hash("Password", PASSWORD_DEFAULT);
$hash2 = password_hash("Password", PASSWORD_DEFAULT);

echo "$hash1<br />$hash2<br />";*/

$items = Category::getItemsByCategory(3);

echo json_encode($items);

echo "</pre>";

?>