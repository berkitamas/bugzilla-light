<?php
if (!defined("PROTECT")) {
    header("Location: ./");
    die;
}

ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

session_start();
require_once "options.php";
require_once "functions.php";
try {
    $db = new PDO('mysql:host=' . $db_host . ';dbname=' . $db_db, $db_user, $db_pass);
    $db->exec("SET CHARACTER SET utf8");
} catch (PDOException $e) {
    die($e->getMessage());
}