<?php
define("PROTECT", true);
require_once "include/init.php";

if (!logged_in()) {
    header("Location: login.php?redirect=" . urlencode($_SERVER['REQUEST_URI']));
    die;
}

session_destroy();
header("Location: ./");