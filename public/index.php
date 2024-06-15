<?php

/**
 * Edinote controller for main page
 */

require '../includes/config.php';

try {
    // Use PDO for database interaction
    $pdo = new PDO($dsn, $user, $pass, $options);

    // Fetch user data
    $userId = $_SESSION["id"];

    $stmt = $pdo->prepare("SELECT username, demo, admin FROM users WHERE id = ?");
    $stmt->execute([$userId]);
    $user = $stmt->fetch();

    if (!$user) {
        throw new Exception('User not found');
    }

    // Store user data in session
    $_SESSION["usrdir"] = DATADIR . $user['username'] . "/";
    $_SESSION["demo"] = $user['demo'];
    $_SESSION["admin"] = $user['admin'];
    $admin = $_SESSION["admin"];

    $users = null;
    if ($admin === "true") {
        $stmt = $pdo->query("SELECT username FROM users");
        $users = $stmt->fetchAll();
    }

    // Get file arrays from database
    $stmt = $pdo->prepare("SELECT fileid, file, tag1, tag2, tag3 FROM files WHERE id = ? ORDER BY LOWER(file)");
    $stmt->execute([$userId]);
    $files = $stmt->fetchAll();

    // Array of filenames contained in db
    $files_db = array_column($files, 'file');

    // Get actual files in user directory
    $files_dir = array_diff(scandir($_SESSION["usrdir"]), ['..', '.']);

    // Compare actual files to files in db
    $diff = array_diff($files_dir, $files_db);
    if (!empty($diff)) {
        $stmt = $pdo->prepare("INSERT INTO files (fileid, id, file, tag1, tag2, tag3) VALUES (?, ?, ?, NULL, NULL, NULL)");
        foreach ($diff as $item) {
            $fileId = uniqid('fid_', true);
            if (!$stmt->execute([$fileId, $userId, $item])) {
                error_log('Could not add files to database');
            }
        }

        // Update files array for later use
        $stmt->execute([$userId]);
        $files = $stmt->fetchAll();
    }

    render("main.php", ["files" => $files, "admin" => $admin, "users" => $users]);
} catch (Exception $e) {
    error_log($e->getMessage());
    // Handle error gracefully, perhaps redirect to an error page or show a user-friendly message
    die('An error occurred. Please try again later.');
}
