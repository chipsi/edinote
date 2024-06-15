<?php

require("../includes/config.php");

$usrdir = $_SESSION['usrdir'];
$rval = null;
$fileEl = null;
$filename = null;
$filename_old = $_POST["filename_old"] ?? null;
$contents = $_POST["contents"] ?? null;
$save_as = $_POST["save_as"] ?? 'false';
$rename = $_POST["rename"] ?? 'false';
$fileId = uniqid('fid_', true);

// Validate filename
if (empty($_POST["filename"])) {
    $rval = 1;
} else {
    $filename = htmlspecialchars($_POST["filename"]);
}

if ($filename !== null && $save_as === 'false') {
    // Overwrite file
    $return = file_put_contents($usrdir . $filename, $contents);

    if ($return !== false) {
        $rval = 0;
    }
} else if ($filename !== null && $save_as === 'true') {
    // Save as new file or rename
    $files_arr = query("SELECT file FROM files WHERE id = ?", $_SESSION["id"]);
    $files = array_column($files_arr, 'file');

    if (in_array($filename, $files)) {
        $rval = 2;
    } else {
        if ($rename === 'false') {
            // Save new file
            $return = file_put_contents($usrdir . $filename, $contents);

            if ($return !== false) {
                // Add new file to database
                $stmt = $pdo->prepare("INSERT INTO files (fileid, id, file, tag1, tag2, tag3) VALUES (?, ?, ?, NULL, NULL, NULL)");
                $result = $stmt->execute([$fileId, $_SESSION["id"], $filename]);

                if ($result) {
                    $rval = 0;
                    ob_start();
                    include '../templates/file_template.php';
                    $fileEl = ob_get_clean();
                } else {
                    $rval = 3;
                }
            }
        } else if ($rename === 'true') {
            // Rename file
            if (rename($usrdir . $filename_old, $usrdir . $filename) !== false) {
                // Update filename in database
                $stmt = $pdo->prepare("UPDATE files SET file = ? WHERE id = ? AND file = ?");
                $result = $stmt->execute([$filename, $_SESSION["id"], $filename_old]);

                if ($result) {
                    $rval = 4;
                } else {
                    $rval = 3;
                }
            }
        }
    }
}

// JSON response
$response = [
    "rval" => $rval,
    "fileId" => $fileId,
    "fileEl" => $fileEl
];

header("Content-type: application/json");
echo json_encode($response);
