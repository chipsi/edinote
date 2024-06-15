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

// Get current view mode
$stmt = $pdo->prepare("SELECT viewmode FROM users WHERE id = ?");
$stmt->execute([$userId]);
$viewmodeData = $stmt->fetch(PDO::FETCH_ASSOC);

if ($viewmodeData === false) {
    http_response_code(503);
    exit;
}

$viewmode = $viewmodeData['viewmode'];

// Switch mode if mode.php was called from switch button
if ($init === 'false') {
    $newViewmode = ($viewmode === 'false') ? 'true' : 'false';
    $stmt = $pdo->prepare("UPDATE users SET viewmode = ? WHERE id = ?");
    if ($stmt->execute([$newViewmode, $userId])) {
        $viewmode = $newViewmode;
    } else {
        $rval = 1;
    }
} else {
    if ($_SESSION['demo'] === 'true') {
        $stmt = $pdo->prepare("UPDATE users SET viewmode = 'false' WHERE id = ?");
        if ($stmt->execute(['false', $userId])) {
            $viewmode = 'false';
        } else {
            $rval = 1;
        }
    }
}

// JSON response
$response = [
    "rval" => $rval,
    "viewmode_r" => $viewmode
];

header("Content-type: application/json");
echo json_encode($response);
