<?php

require("../includes/config.php");

$filename = $_POST["filename"] ?? null;
$fileId = $_POST["fileId"] ?? null;
$tagged = false;
$tag_num = null;
$tagId = null;
$rval = null;

if (empty($_POST["tag"])) {
    $rval = 1;
} else {
    $tag = htmlspecialchars($_POST["tag"]);
}

// Prepare and execute the SELECT statement to get tag slots
$stmt = $pdo->prepare("SELECT tag1, tag2, tag3 FROM files WHERE id = ? AND file = ?");
$stmt->execute([$_SESSION["id"], $filename]);
$tags = $stmt->fetch(PDO::FETCH_ASSOC);

if ($tags) {
    // Try all tag slots
    for ($i = 1; $i <= 3; $i++) {
        $tagColumn = "tag$i";
        if ($tags[$tagColumn] === null) {
            $tag_num = $tagColumn;
            $stmtUpdate = $pdo->prepare("UPDATE files SET $tagColumn = ? WHERE id = ? AND file = ?");
            $tagged = $stmtUpdate->execute([$tag, $_SESSION["id"], $filename]);
            break;
        }
    }

    if ($tag_num !== null && $tagged) {
        $rval = 0;
        $tagId = $tag_num . '_' . $fileId;
    } elseif ($tag_num === null) {
        $rval = 3; // No available tag slots
    } else {
        $rval = 2; // Update failed
    }
} else {
    $rval = 4; // File not found
}

// JSON response
$response = [
    "rval" => $rval,
    "tagId" => $tagId
];

header("Content-type: application/json");
echo json_encode($response);
