<?php

require("../includes/config.php");

$rval = NULL;

if ($_SESSION['demo'] === 'true') {
    $rval = 5;
} else {
    if (empty($_POST["pw"])) {
        // password is empty
        $rval = 1;
    } else if (empty($_POST["conf"])) {
        // confirmation is empty
        $rval = 2;
    } else if ($_POST["pw"] !== $_POST["conf"]) {
        // confirmation does not match password
        $rval = 3;
    } else {
        // update user's password
        $hashedPassword = password_hash($_POST["pw"], PASSWORD_DEFAULT);
        $stmt = $pdo->prepare("UPDATE users SET hash = ? WHERE id = ?");
        if ($stmt->execute([$hashedPassword, $_SESSION["id"]])) {
            $rval = 0;
        } else {
            $rval = 4;
        }
    }
}

// build array for ajax response
$response = [
    "rval" => $rval
];

// spit out content as json
header("Content-type: application/json");
echo json_encode($response);
