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

function validEmail($email) {
    return filter_var($email, FILTER_VALIDATE_EMAIL);
}

function validUsername($username) {
    $usernameLength = strlen($username);
    if ($usernameLength < 4 || $usernameLength > 28) {
        return false;
    }

    if (preg_match("/[^a-zA-Z0-9]/", $username)) {
        return false;
    }

    return true;
}

function validPassword($password) {
    $passwordLength = strlen($password);
    if ($passwordLength < 8 || $passwordLength > 32) {
        return false;
    }

    if (!preg_match("/[A-Z]/", $password)) {
        return false;
    }

    if (!preg_match("/[^a-zA-Z0-9]/", $password)) {
        return false;
    }

    return true;
}

function validUser($conn, $email, $username) {
    $query = "SELECT COUNT(*) AS count FROM users WHERE email = ? OR username = ?";
    $stmt = $conn->prepare($query);
    $stmt->bind_param("ss", $email, $username);
    $stmt->execute();
    $result = $stmt->get_result()->fetch_assoc();
    $stmt->close();

    return $result["count"] > 0;
}

function createUser($conn, $email, $username, $password) {
    $hashed_password = password_hash($password, PASSWORD_DEFAULT);

    $query = "INSERT INTO users (email, username, `password`) VALUES (?, ?, ?)";
    $stmt = $conn->prepare($query);
    $stmt->bind_param("sss", $email, $username, $hashed_password);
    $stmt->execute();
    $stmt->close();
}

if ($_SERVER["REQUEST_METHOD"] !== "POST") {
    http_response_code(405);
    echo "Method Not Allowed";
    exit();
}

$conn = connectToDatabase();

$email = $_POST["email"] ?? "";
$username = $_POST["username"] ?? "";
$password = $_POST["password"] ?? "";

if (!validEmail($email)) {
    http_response_code(400);
    echo "Invalid Email";
} elseif (!validUsername($username)) {
    http_response_code(400);
    echo "Invalid Username";
} elseif (!validPassword($password)) {
    http_response_code(400);
    echo "Invalid Password";
} elseif (validUser($conn, $email, $username)) {
    http_response_code(409);
    echo "User Already Exists";
} else {
    createUser($conn, $email, $username, $password);
    echo "User Created";
}

$conn->close();

?>
