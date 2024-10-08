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

function validHead($head) {
    $headLength = strlen($head);
    return $headLength > 0 && $headLength <= 80;
}

function validBody($body) {
    $bodyLength = strlen($body);
    return $bodyLength > 0 && $bodyLength <= 280;
}

function createPost($conn, $userid, $head, $body) {
    $query = "INSERT INTO content (`head`, `body`) VALUES (?, ?)";
    $stmt = $conn->prepare($query);
    $stmt->bind_param("ss", $head, $body);
    $stmt->execute();
    $contentid = $stmt->insert_id;
    $stmt->close();

    $query = "INSERT INTO posts (contentid, userid) VALUES (?, ?)";
    $stmt = $conn->prepare($query);
    $stmt->bind_param("ii", $contentid, $userid);
    $stmt->execute();
    $stmt->close();

    $query = "SELECT users.username, posts.*, content.* FROM users 
              JOIN posts ON users.userid = posts.userid 
              JOIN content ON posts.contentid = content.contentid 
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
$head = $_POST["head"] ?? "";
$body = $_POST["body"] ?? "";

if (!validId($userid)) {
    http_response_code(401);
    echo "Invalid UserID";
} elseif (!validHead($head)) {
    http_response_code(400);
    echo "Invalid Head";
} elseif (!validBody($body)) {
    http_response_code(400);
    echo "Invalid Body";
} else {
    $result = createPost($conn, $userid, $head, $body);
    echo json_encode($result);
}

$conn->close();

?>
