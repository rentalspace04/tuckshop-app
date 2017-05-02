<?php

class Page {
    private $title;
    private $user;
    private $scripts = array();
    private $styles = array();
    private $menu = array();
    private $DEFAULT_MENU = array(array("/account.php", "My<br />Account"), array("/logout.php", "Log<br />Out"));

    public function __construct($title, $user) {
        $this->title = $title;
        $this->user = $user;
    }

    public function getUser() {
        return $this->user;
    }

    public function getTitle() {
        return $this->title;
    }

    public function isLoggedIn() {
        return !is_null($this->user);
    }

    public function addScript($scriptLocation) {
        // Add script to list
        $this->scripts[] = $scriptLocation;
    }

    public function getScripts() {
        $scriptOutput = "";

        foreach($this->scripts as $script) {
            $scriptOutput = $scriptOutput . "<script src='$script'></script>\n";
        }

        return $scriptOutput;
    }

    public function addStyle($styleLocation) {
        // Add script to list
        $this->styles[] = $styleLocation;
    }

    public function getStyles() {
        $styleOutput = "";

        foreach($this->styles as $style) {
            $styleOutput = $styleOutput . "link rel=\"stylesheet\" type=\"text/css\" href=\"$style\" />\n";
        }

        return $styleOutput;
    }

    public function addMenuItem($link, $text) {
        $this->menu[] = array($link, $text);
    }

    public function getMenuItems() {
        $out = "";
        $items = count($this->menu) == 0 ? $this->DEFAULT_MENU : $this->menu;
        foreach ($items as $item) {
            $link = $item[0];
            $text = $item[1];
            $out .= "<a href=\"$link\">$text</a>\n";
        }
        return $out;
    }
}

$UNTITLED_PAGE = new Page("Untitled", NULL);

?>