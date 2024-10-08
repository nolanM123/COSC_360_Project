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

function hasLiked($conn, $userid, $contentid) {
    $query = "SELECT COUNT(*) AS count FROM likes WHERE contentid = ? AND userid = ?";
    $stmt = $conn->prepare($query);
    $stmt->bind_param("ii", $contentid, $userid);
    $stmt->execute();
    $result = $stmt->get_result()->fetch_assoc();
    $stmt->close();

    return $result["count"] > 0;
}

function hasCommented($conn, $userid, $contentid) {
    $query = "SELECT COUNT(*) AS count FROM comments WHERE parentid= ? AND userid = ?";
    $stmt = $conn->prepare($query);
    $stmt->bind_param("ii", $contentid, $userid);
    $stmt->execute();
    $result = $stmt->get_result()->fetch_assoc();
    $stmt->close();

    return $result["count"] > 0;
}

function hasShared($conn, $userid, $contentid) {
    $query = "SELECT COUNT(*) AS count FROM shares WHERE contentid = ? AND userid = ?";
    $stmt = $conn->prepare($query);
    $stmt->bind_param("ii", $contentid, $userid);
    $stmt->execute();
    $result = $stmt->get_result()->fetch_assoc();
    $stmt->close();

    return $result["count"] > 0;
}

session_start();

if ($_SERVER["REQUEST_METHOD"] !== "GET") {
    http_response_code(405);
    echo "Method Not Allowed";
    exit();
}

$conn = connectToDatabase();

$userid = $_GET["userid"] ?? 0;
$contentid = $_GET["contentid"] ?? 0;

if (!validId($userid)) {
    http_response_code(400);
    echo "Invalid UserID";
} elseif (!validId($contentid)) {
    http_response_code(400);
    echo "Invalid ContentID";
} else {
    $data = [];
    $data["liked"] = hasLiked($conn, $userid, $contentid);
    $data["commented"] = hasCommented($conn, $userid, $contentid);
    $data["shared"] = hasShared($conn, $userid, $contentid);
    echo json_encode($data);
}

$conn->close();

?>
