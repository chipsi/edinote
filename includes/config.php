<?php

/**
 * Edinote config.php
 */

// display errors, warnings, and notices
ini_set("display_errors", true);
error_reporting(E_ALL);

// requirements
require("constants.php");
require("functions.php");

// enable sessions
session_start();

// Database configuration
$host = SERVER;        // Database host
$db = DATABASE;        // Database name
$user = USERNAME;      // Database username
$pass = PASSWORD;      // Database password
$charset = 'utf8mb4';

$dsn = DBTYPE === 'mysql'
    ? "mysql:host=$host;dbname=$db;charset=$charset"
    : "sqlite:" . DATADIR . $db;

$options = [
    PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
    PDO::ATTR_EMULATE_PREPARES   => false,
];

// Create a PDO instance
try {
    $pdo = new PDO($dsn, $user, $pass, $options);
} catch (Exception $e) {
    trigger_error($e->getMessage(), E_USER_ERROR);
    exit;
}

// require authentication for all pages except /login.php and /logout.php
if (!in_array($_SERVER["PHP_SELF"], ["/login.php", "/logout.php"])) {
    if (empty($_SESSION["id"])) {
        redirect("login.php");
    }
}
