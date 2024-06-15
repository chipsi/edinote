<?php

require("../includes/config.php");

$usrdir = $_SESSION['usrdir'] ?? null;
$fileId = $_GET["fileId"] ?? null;

// Ensure user directory and file ID are set
if (empty($usrdir) || empty($fileId)) {
    http_response_code(400);
    exit;
}

// Get name of file
$stmt = $pdo->prepare("SELECT file FROM files WHERE fileid = ?");
$stmt->execute([$fileId]);
$fileData = $stmt->fetch(PDO::FETCH_ASSOC);

// Ensure file exists in database
if (!$fileData) {
    http_response_code(404);
    exit;
}

$filename = $fileData['file'];

// Extract content of file
$content = file_get_contents($usrdir . $filename);

// Ensure content extraction did work
if ($content === false) {
    http_response_code(503);
    exit;
}

// Build array for ajax response
$response = [
    "content" => $content,
    "filename" => $filename
];

// Output content as JSON
header("Content-type: application/json");
echo json_encode($response);
