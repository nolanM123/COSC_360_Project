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

function getPosts($conn, $username=null, $contentid=null, $userid=null, $terms=null, $timestamp1=null, $timestamp2=null) {
    $query = "SELECT users.role, users.username, posts.*, content.* FROM users 
              JOIN posts ON users.userid = posts.userid 
              JOIN content ON posts.contentid = content.contentid 
              WHERE 1 = ?";
    $params = "i";
    $values = [1];

    if (!is_null($username)) {
        $query .= " AND username LIKE ?";
        $params .= "s";
        $values[] = "%" . $username . "%";
    }
    if (!is_null($contentid)) {
        $query .= " AND content.contentid = ?";
        $params .= "i";
        $values[] = $contentid;
    }
    if (!is_null($userid)) {
        $query .= " AND users.userid = ?";
        $params .= "i";
        $values[] = $userid;
    }
    if (!is_null($terms)) {
        $terms = json_decode($terms);
        foreach($terms as $term) {
            $query .= " AND (`head` LIKE ? OR `body` LIKE ?)";
            $params .= "ss";
            $term = "%" . $term . "%";
            $values[] = $term;
            $values[] = $term;
        }
    }
    if (!is_null($timestamp1) && !is_null($timestamp2)) {
        $query .= " AND creationdate NOT BETWEEN ? AND ?";
        $params .= "ss";
        $values[] = $timestamp1;
        $values[] = $timestamp2;
    } elseif (!is_null($timestamp1)) {
        $query .= " AND creationdate < ?";
        $params .= "s";
        $values[] = $timestamp1;
    } elseif (!is_null($timestamp2)) {
        $query .= " AND creationdate > ?";
        $params .= "s";
        $values[] = $timestamp2;
    }

    $query .= " ORDER BY creationdate DESC LIMIT 5";
    $stmt = $conn->prepare($query);
    $stmt->bind_param($params, ...$values);
    $stmt->execute();
    $result = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
    $stmt->close();

    return $result;
}

if ($_SERVER["REQUEST_METHOD"] !== "GET") {
    http_response_code(405);
    echo "Method Not Allowed";
    exit();
}

$conn = connectToDatabase();

$username = $_GET["username"] ?? NULL;
$contentid = $_GET["contentid"] ?? NULL;
$userid = $_GET["userid"] ?? NULL;
$terms = $_GET["terms"] ?? NULL;
$timestamp1 = $_GET["timestamp1"] ?? NULL;
$timestamp2 = $_GET["timestamp2"] ?? NULL;

$result = getPosts($conn, $username, $contentid, $userid, $terms, $timestamp1, $timestamp2);
echo json_encode($result);

$conn->close();

?>
