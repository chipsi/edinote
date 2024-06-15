<?php

require("../includes/config.php");

$rval = NULL;

// Retrieve and sanitize POST data
$name = $_POST["name"] ?? null;
$pw = $_POST["pw"] ?? null;
$adm = $_POST["adm"] ?? 'false'; // Default to 'false' if not set

if ($_SESSION['admin'] === 'false') {
    // user is not admin
    $rval = 1;
} else {
    if (empty($name) || empty($pw)) {
        // not all fields have been filled
        $rval = 2;
    } else {
        // Hash the password
        $hashedPassword = password_hash($pw, PASSWORD_DEFAULT);

        // Prepare and execute the insert statement
        $stmt = $pdo->prepare("INSERT INTO users (username, hash, admin, demo, viewmode, defaultext) VALUES (?, ?, ?, 'false', 'false', 'md')");
        if ($stmt->execute([$name, $hashedPassword, $adm])) {
            // User was successfully added to db; create user directory
            if (mkdir(DATADIR . $name) !== false) {
                // Success
                $rval = 0;
            } else {
                // Could not create directory
                $rval = 4;
            }
        } else {
            // Username already exists
            $rval = 3;
        }
    }
}

// Build array for ajax response
$response = [
    "rval" => $rval
];

// Spit out content as JSON
header("Content-type: application/json");
echo json_encode($response);
