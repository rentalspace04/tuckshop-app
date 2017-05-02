<?php

include_once $_SERVER['DOCUMENT_ROOT'] . "/lib/empty_page.php";

?>

<head>
    <meta charset="utf-8">
    <title><?php echo $page->getTitle();?></title>
    <link rel="stylesheet" type="text/css" href="/styles/main.css" />
    <?php echo $page->getStyles(); ?>
    <link rel="icon" href="/img/favicon.ico" type="image/x-icon" />
    <link rel="icon" type="image/png" href="/img/favicon-32x32.png" sizes="32x32">
    <link rel="icon" type="image/png" href="/img/favicon-16x16.png" sizes="16x16">
    <script src="/js/jquery-3.2.1.min.js"></script>
    <script src="/js/prototype.js"></script>
    <?php echo $page->getScripts(); ?>
</head>