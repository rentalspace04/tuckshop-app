<?php
    session_start();

    include_once $_SERVER['DOCUMENT_ROOT'] . "/lib/page.php";
    include_once $_SERVER['DOCUMENT_ROOT'] . "/lib/helper.php";
    include_once $_SERVER['DOCUMENT_ROOT'] . "/lib/items.php";
    include_once $_SERVER['DOCUMENT_ROOT'] . "/lib/order.php";

    // Check that we aren't logged in - if we are, redirect to home.php
    if (!Helper::isLoggedIn()) {
        Helper::redirect("/login.php?redirect=/history/index.php");
    }

    $user = Helper::getLoggedInUser();

    // Setup that this is the Home page
    $page = new Page("Order History", $user);

    // $page->addScript("/js/class.js");
    // $page->addScript("/js/history.js");

    $page->addMenuItem("/index.php", "Home");
    $page->addMenuItem("/order/index.php", "Place<br />Order");
    $page->addMenuItem("/logout.php", "Log<br />Out");

    // Show page 1 of results by default
    $showPage = 1;

    if (isset($_GET["page"]) && Helper::isInteger($_GET["page"])) {
        $showPage = $_GET["page"];
    }

    $orders = Order::getUserOrderIDs($user->userID);

    // Show 10 orders per page
    // If there are less orders than should be on this page, just show page 1
    if (count($orders) < 10 * $showPage) {
        //
    }

    function getOrderTable($user, $orders, $showPage) {
        $out = "";
        $out .= "<table id=\"orderHistory\">\n";
        $out .= "<thead>\n";
        $out .= "<tr>\n";
        $out .= getHeaderRow($user);
        $out .= "</tr>\n";
        $out .= "</thead>\n";
        $out .= "<tbody>\n";
        $out .= getOrderRows($user, $orders, $showPage);
        $out .= "</tbody>\n";
        $out .= "</table>\n";

        return $out;
    }

    function getHeaderRow($user) {
        $out = "";

        $out .= "<th>Order No.</th>\n";
        if ($user->type == User::$PARENT) {
            $out .= "<th>For</th>\n";
        }
        $out .= "<th>Date Ordered</th>\n";
        $out .= "<th>Price</th>\n";
        $out .= "<th></th>\n";

        return $out;
    }

    function getOrderRows($user, $orders, $pageNum) {
        $out = "";

        $startOrder = $pageNum * 10 - 10;
        $endOrder = min($pageNum * 10, count($orders));

        for ($i = $startOrder; $i < $endOrder; $i++) {
            $out .= getOrderRow($orders[$i], $user->type == User::$PARENT);
        }

        return $out;
    }

    function getOrderRow($orderID, $isParent) {
        //
        $out = "";

        $order = Order::getOrderById($orderID);

        $out .= "<tr><td>#$order->orderID</td>\n";
        if ($isParent) {
            $madeForUser = User::getById($order->madeFor);
            $out .= "<td>$madeForUser->firstName</td>\n";
        }
        $dateString = date("d/m/y h:i A", strtotime($order->orderedAt));
        $out .= "<td>$dateString</td>\n";
        $price = number_format($order->getCost(), 2);
        $out .= "<td>\$$price</td>";

        $out .="<td><a href=\"/history/view.php?id=$order->orderID\" class=\"button small\">View Order</a></td></tr>\n";

        return $out;
    }

    function getPageList($orders, $pageNum) {
        $numOfPages = ceil(count($orders) / 10);
        $showPages = getPagesToShow($pageNum, $numOfPages);

        $out = "";

        $buttonEnabled = $pageNum != $numOfPages;

        $out .= "<div id=\"pageListContainer\">\n";

        $buttonEnabled = $pageNum != 1;
        $prevPage = $pageNum - 1;
        $link = $buttonEnabled ? "href=\"/history/index.php?page=$prevPage\"" : "";
        $disabled = $buttonEnabled ? "" : "disabled";
        $out .= "<a class=\"button small $disabled\" $link >Prev</a>\n";

        $out .= "<div id=\"pageList\">";

        foreach ($showPages as $thisPage) {
            $current = $thisPage == $pageNum ? "currentPage" : "";
            $link = $thisPage == $pageNum ? "" : "href=\"/history/index.php?page=$thisPage\"";
            $out .= "<a class=\"pageListButton $current\" $link >$thisPage</a>\n";
        }

        $out .= "</div>\n";

        $buttonEnabled = $pageNum != $numOfPages;
        $nextPage = $pageNum + 1;
        $link = $buttonEnabled ? "href=\"/history/index.php?page=$nextPage\"" : "";
        $disabled = $buttonEnabled ? "" : "disabled";
        $out .= "<a class=\"button small $disabled\" $link >Next</a>\n</div>";

        return $out;
    }

    function getPagesToShow($pageNum, $numOfPages) {
        $showPages = array();

        if ($numOfPages <= 9) {
            for ($i = 1; $i <= $numOfPages; $i++) {
                $showPages[] = $i;
            }
        } else {
            // Show 1st page, last page + 7 around current page
            // When current page is page 1, show next 7 pages
            // When current page is last page, show 7 prev pages
            if ($pageNum == 1) {
                $showPages = getRange(1, 7);
                $showPages[] = $numOfPages;
            } else if ($pageNum == $numOfPages) {
                $showPages[] = 1;
                $showPages = getRange($numOfPages - 6, $numOfPages, $showPages);
            } else {
                // current page is intermediate one
                $showPages[] = 1;
                // Get intermediate range
                $startRange = getRangeStart($pageNum, $numOfPages);
                $endRange = getRangeEnd($pageNum, $numOfPages);
                $showPages = getRange($startRange, $endRange, $showPages);
                $showPages[] = $numOfPages;
            }
        }

        return $showPages;
    }

    // Returns the start of the intermediate section of pages for the page list
    // i.e. For set w/ 20 pages
    //     for cp =  2, should return 2
    //     for cp =  5, should return 2
    //     for cp = 10, should return 7
    //     for cp = 16, should return 13
    //     for cp = 17, should return 13
    function getRangeStart($currentPage, $numOfPages) {
        if ($currentPage - 4 <= 1) {
            return 2;
        } else if ($currentPage + 4 >= $numOfPages - 1) {
            return $numOfPages - 7;
        }
        return $currentPage - 3;
    }

    // Returns the end of the intermediate section of pages for the page list
    // i.e. For set w/ 20 pages
    //     for cp =  2, should return 8
    //     for cp =  5, should return 8
    //     for cp = 10, should return 13
    //     for cp = 16, should return 19
    //     for cp = 17, should return 19
    function getRangeEnd($currentPage, $numOfPages) {
        if ($currentPage - 4 <= 1) {
            return 8;
        } else if ($currentPage + 4 >= $numOfPages - 1) {
            return $numOfPages - 1;
        }
        return $currentPage + 3;
    }

    function getRange($start, $end, $array = array()) {
        for ($i = $start; $i <= $end; $i++) {
            $array[] = $i;
        }
        return $array;
    }

    include_once $_SERVER['DOCUMENT_ROOT'] . "/templates/main_start.php";

    echo "<h1>Order History</h1>\n";

    if (count($orders) == 0) {
        echo "<p>You haven't made any orders!</p>";
    } else {
        echo getOrderTable($user, $orders, $showPage);
        echo getPageList($orders, $showPage);
    }

    include_once $_SERVER['DOCUMENT_ROOT'] . "/templates/main_end.php";
?>