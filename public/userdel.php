<?php

require("../includes/config.php");

$rval = null;
$name = $_POST["name"] ?? null;
$dir = DATADIR . $name;

if ($_SESSION['admin'] === 'false') {
    // user is not admin
    $rval = 1;
} else {
    if ($name === "Select..." || empty($name)) {
        // no user selected
        $rval = 2;
    } else {
        // Prepare and execute the SELECT statement
        $stmt = $pdo->prepare("SELECT id FROM users WHERE username = ?");
        $stmt->execute([$name]);
        $user = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($user) {
            $id = $user['id'];

            // Prepare and execute the DELETE statements
            $stmtDelUser = $pdo->prepare("DELETE FROM users WHERE username = ?");
            $stmtDelFiles = $pdo->prepare("DELETE FROM files WHERE id = ?");

            $delUser = $stmtDelUser->execute([$name]);
            $delFiles = $stmtDelFiles->execute([$id]);

            if ($delUser && $delFiles) {
                // User was successfully deleted from db; delete user files
                foreach (scandir($dir) as $file) {
                    if ($file === '.' || $file === '..') continue;
                    if (is_dir("$dir/$file")) {
                        rmdir_recursive("$dir/$file");
                    } else {
                        unlink("$dir/$file");
                    }
                }
                // delete user directory
                if (rmdir($dir) !== false) {
                    // success
                    $rval = 0;
                } else {
                    // could not delete directory
                    $rval = 4;
                }
            } else {
                // something went wrong
                $rval = 3;
            }
        } else {
            // user not found
            $rval = 3;
        }
    }
}

// Function to recursively delete a directory
function rmdir_recursive($dir)
{
    foreach (scandir($dir) as $file) {
        if ($file === '.' || $file === '..') continue;
        if (is_dir("$dir/$file")) {
            rmdir_recursive("$dir/$file");
        } else {
            unlink("$dir/$file");
        }
    }
    rmdir($dir);
}

// Build array for ajax response
$response = [
    "rval" => $rval
];

// Spit out content as JSON
header("Content-type: application/json");
echo json_encode($response);
