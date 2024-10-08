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

function liked($conn, $userid, $contentid) {
    $query = "SELECT COUNT(*) as count FROM likes WHERE contentid = ? AND userid = ?";
    $stmt = $conn->prepare($query);
    $stmt->bind_param("ii", $contentid, $userid);
    $stmt->execute();
    $result = $stmt->get_result()->fetch_assoc();
    $stmt->close();

    return $result["count"] > 0;
}

function like($conn, $userid, $contentid) {
    $query = "UPDATE content SET likes = likes + 1 WHERE contentid = ?";
    $stmt = $conn->prepare($query);
    $stmt->bind_param("i", $contentid);
    $stmt->execute();
    $stmt->close();

    $query = "INSERT INTO likes (contentid, userid) VALUES (?, ?)";
    $stmt = $conn->prepare($query);
    $stmt->bind_param("ii", $contentid, $userid);
    $stmt->execute();
    $stmt->close();
}

function unlike($conn, $userid, $contentid) {
    $query = "UPDATE content SET likes = likes - 1 WHERE contentid = ?";
    $stmt = $conn->prepare($query);
    $stmt->bind_param("i", $contentid);
    $stmt->execute();
    $stmt->close();

    $query = "DELETE FROM likes WHERE contentid = ? AND userid = ?";
    $stmt = $conn->prepare($query);
    $stmt->bind_param("ii", $contentid, $userid);
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

$userid = $_SESSION["userid"] ?? 0;
$contentid = $_POST["contentid"] ?? 0;

if (!validId($userid)) {
    http_response_code(401);
    echo "Invalid UserID";
} elseif (!validId($contentid)) {
    http_response_code(400);
    echo "Invalid ContentID";
} elseif (liked($conn, $userid, $contentid)) {
    unlike($conn, $userid, $contentid);
    echo "Unliked";
} else {
    like($conn, $userid, $contentid);
    echo "Liked";
}

$conn->close();

?>
