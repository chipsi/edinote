<?php

require("../includes/config.php");

$rval = null;

if (empty($_POST["tagId"])) {
    $rval = 1;
} else {
    // Extract the last 17 digits to get the file ID
    $fileId = substr($_POST["tagId"], -17);
    // Extract the first 4 digits to get tag number
    $tag = substr($_POST["tagId"], 0, 4);

    // Ensure $tag is valid before using it in the query
    $validTags = ['tag1', 'tag2', 'tag3'];
    if (in_array($tag, $validTags)) {
        $stmt = $pdo->prepare("UPDATE files SET {$tag} = NULL WHERE id = ? AND fileid = ?");
        $result = $stmt->execute([$_SESSION["id"], $fileId]);

        if ($result !== false) {
            $rval = 0;
        } else {
            $rval = 2;
        }
    } else {
        $rval = 3; // Invalid tag
    }
}

$response = [
    "rval" => $rval,
    "file" => $fileId,
    "tag" => $tag
];

header("Content-type: application/json");
echo json_encode($response);
