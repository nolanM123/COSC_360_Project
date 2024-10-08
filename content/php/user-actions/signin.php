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

function getUser($conn, $identity) {
    $query = "SELECT userid, `role`, username, `password` FROM users WHERE username = ? OR email = ?";
    $stmt = $conn->prepare($query);
    $stmt->bind_param("ss", $identity, $identity);
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

$identity = $_POST["identity"] ?? "";
$password = $_POST["password"] ?? "";

if (!validEmail($identity) && !validUsername($identity)) {
    http_response_code(400);
    echo "Invalid Identity";
} elseif (!validPassword($password)) {
    http_response_code(400);
    echo "Invalid Password";
} else {
    $result = getUser($conn, $identity);
    if ($result) {
        $userid = $result["userid"];
        $role = $result["role"];
        $username = $result["username"];
        $encryptedPassword = $result["password"];

        if (password_verify($password, $encryptedPassword)) {
            $_SESSION["userid"] = $userid;
            $_SESSION["role"] = $role;
            setcookie("userid", $userid, time() + 86400, "/");
            setcookie("role", $role, time() + 86400, "/");
            setcookie("username", $username, time() + 86400, "/");
            echo "User Found";
        } else {
            http_response_code(409);
            echo "User Does Not Exist";
        }
    } else {
        http_response_code(409);
        echo "User Does Not Exist";
    }
}

$conn->close();

?>