<?php

require_once '../.config.php';

function connectToDatabase() {
    // $servername = "localhost";
    // $username = "12588786";
    // $password = "temporarypassword";
    // $database = "db_12588786";
    $servername = DBHOST;
    $username = DBUSER;
    $password = DBPASS;
    $database = DBNAME;

    $conn = new mysqli($servername, $username, $password, $database);
    if ($conn->connect_error) {
        http_response_code(500);
        echo "Connection failed: " . $conn->connect_error;
        exit();
    }

    return $conn;
}

function validId($id) {
    return $id > 0;
}

function validBody($body) {
    $bodyLength = strlen($body);
    return $bodyLength > 0 && $bodyLength <= 280;
}

function owner($conn, $userid, $contentid) {
    $query = "SELECT COUNT(*) as count 
              FROM content
              JOIN comments ON content.contentid = comments.contentid
              WHERE content.contentid = ? AND userid = ?";
    $stmt = $conn->prepare($query);
    $stmt->bind_param("ii", $contentid, $userid);
    $stmt->execute();
    $result = $stmt->get_result()->fetch_assoc();
    $stmt->close();

    return $result["count"] > 0;
}

function updatePost($conn, $contentid, $body) {
    $query = "UPDATE content SET `body` = ? WHERE contentid = ?";
    $stmt = $conn->prepare($query);
    $stmt->bind_param("ssi", $body, $contentid);
    $stmt->execute();
    $stmt->close();
}

session_start();

if ($_SERVER["REQUEST_METHOD"] !== "POST") {
    http_response_code(405);
    echo "Method Not Allowed";
    exit();
}

$conn = connectToDatabase();

$role = $_SESSION["role"] ?? "user";
$userid = $_SESSION["userid"] ?? 0;
$contentid = $_POST["contentid"] ?? 0;
$body = $_POST["body"] ?? "";

if (!validId($userid)) {
    http_response_code(401);
    echo "Invalid UserID";
} elseif (!validId($contentid)) {
    http_response_code(400);
    echo "Invalid ContentID";
} elseif (!validBody($body)) {
    http_response_code(400);
    echo "Invalid Body";
} elseif ($role === "admin" || owner($conn, $userid, $contentid)) {
    updatePost($conn, $contentid, $head, $body);
    echo "Post Updated";
} else {
    http_response_code(403);
    echo "Invalid UserID with ContentID";
}

$conn->close();

?>