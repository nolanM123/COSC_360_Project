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

function validBody($body) {
    $bodyLength = strlen($body);
    return $bodyLength > 0 && $bodyLength <= 280;
}

function createComment($conn, $userid, $parentid, $body) {
    $query = "UPDATE content SET comments = comments + 1 WHERE contentid = ?";
    $stmt = $conn->prepare($query);
    $stmt->bind_param("i", $parentid);
    $stmt->execute();
    $stmt->close();

    $query = "INSERT INTO content (`body`) VALUES (?)";
    $stmt = $conn->prepare($query);
    $stmt->bind_param("s", $body);
    $stmt->execute();
    $contentid = $stmt->insert_id;
    $stmt->close();

    $query = "INSERT INTO comments (contentid, parentid, userid) VALUES (?, ?, ?)";
    $stmt = $conn->prepare($query);
    $stmt->bind_param("iii", $contentid, $parentid, $userid);
    $stmt->execute();
    $stmt->close();

    $query = "SELECT users.username, comments.*, content.* FROM users 
              JOIN comments ON users.userid = comments.userid 
              JOIN content ON comments.contentid = content.contentid 
              WHERE content.contentid = ?";
    $stmt = $conn->prepare($query);
    $stmt->bind_param("i", $contentid);
    $stmt->execute();
    $result = $stmt->get_result()->fetch_assoc();
    $stmt->close();

    return $result;
}

session_start();

if ($_SERVER["REQUEST_METHOD"] !== "POST") {
    http_response_code(405);
    echo "Method Not Allowed";
    exit();
}

$conn = connectToDatabase();

$userid = $_SESSION["userid"] ?? 0;
$parentid = $_POST["parentid"] ?? 0;
$body = $_POST["body"] ?? "";

if (!validId($userid)) {
    http_response_code(401);
    echo "Invalid UserID";
} elseif (!validId($parentid)) {
    http_response_code(400);
    echo "Invalid ParentID";
} elseif (!validBody($body)) {
    http_response_code(400);
    echo "Invalid Body";
} else {
    $result = createComment($conn, $userid, $parentid, $body);
    echo json_encode($result);
}

$conn->close();

?>
