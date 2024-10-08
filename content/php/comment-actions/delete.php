<?php

require_once '../../../../config.php';

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

function deleteComment($conn, $contentid) {
    deleteComments($conn, $contentid);

    $query = "SELECT parentid FROM comments WHERE contentid = ?";
    $stmt = $conn->prepare($query);
    $stmt->bind_param("i", $contentid);
    $stmt->execute();
    $result = $stmt->get_result()->fetch_assoc();
    $stmt->close();

    $parentid = $result["parentid"];

    $query = "UPDATE content SET comments = comments - 1 WHERE contentid = ?";
    $stmt = $conn->prepare($query);
    $stmt->bind_param("i", $parentid);
    $stmt->execute();
    $stmt->close();

    $query = "DELETE FROM content WHERE contentid = ?";
    $stmt = $conn->prepare($query);
    $stmt->bind_param("i", $contentid);
    $stmt->execute();
    $stmt->close();
}

function deleteComments($conn, $parentid) {
    $query = "SELECT contentid FROM comments WHERE parentid = ?";
    $stmt = $conn->prepare($query);
    $stmt->bind_param("i", $parentid);
    $stmt->execute();
    $results = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
    $stmt->close();

    foreach ($results as $result) {
        $contentid = $result["contentid"];
        deleteComments($conn, $contentid);

        $query = "DELETE FROM content WHERE contentid = ?";
        $stmt = $conn->prepare($query);
        $stmt->bind_param("i", $contentid);
        $stmt->execute();
        $stmt->close();    
    }
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

if (!validId($userid)) {
    http_response_code(401);
    echo "Invalid UserID";
} elseif (!validId($contentid)) {
    http_response_code(400);
    echo "Invalid ContentID";
} elseif ($role === "admin" || owner($conn, $userid, $contentid)) {
    deleteComment($conn, $contentid);
    echo "Comment Deleted";
} else {
    http_response_code(403);
    echo "Invalid UserID with ContentID";
}

$conn->close();

?>
