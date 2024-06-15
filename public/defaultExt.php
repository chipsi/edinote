<?php

require("../includes/config.php");

$rval = null;
$init = $_POST["init"] ?? null;
$userId = $_SESSION["id"] ?? null;

// Validate user session
if ($userId === null) {
    http_response_code(400);
    exit;
}

// Get current default extension
$stmt = $pdo->prepare("SELECT defaultext FROM users WHERE id = ?");
$stmt->execute([$userId]);
$defaultExtData = $stmt->fetch(PDO::FETCH_ASSOC);

if ($defaultExtData === false) {
    http_response_code(503);
    exit;
}

$defaultExt = $defaultExtData['defaultext'];

// Switch default extension if defaultExt.php was called from account settings
if ($init === 'false') {
    $extDefault = $_POST["extDefault"] ?? null;

    if ($extDefault === null) {
        http_response_code(400);
        exit;
    }

    $stmt = $pdo->prepare("UPDATE users SET defaultext = ? WHERE id = ?");
    if ($stmt->execute([$extDefault, $userId])) {
        $rval = 0;
    } else {
        $rval = 1;
    }
} else {
    // If initial page load and demo user, always set to 'md'
    if ($_SESSION['demo'] === 'true') {
        $stmt = $pdo->prepare("UPDATE users SET defaultext = 'md' WHERE id = ?");
        if ($stmt->execute([$userId])) {
            $defaultExt = 'md';
            $rval = 0;
        } else {
            $rval = 1;
        }
    }
}

// JSON response
$response = [
    "rval" => $rval,
    "demo" => $_SESSION["demo"],
    "ext" => $defaultExt
];

header("Content-type: application/json");
echo json_encode($response);
