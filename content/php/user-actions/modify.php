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

function validImage($usericon){
    if (!is_null($usericon)) {
        $extension = pathinfo($usericon, PATHINFO_EXTENSION);
        if($extension != 'png'){
            return false;
        }
        else{
            return true;
        }
    }
    return false; 
}

function modifyUser($conn, $userid, $usericon=NULL, $email=NULL, $username=NULL, $password=NULL) {
    if (!is_null($usericon)) {
        $tempFile = $usericon["tmp_name"];
        $fileType = pathinfo($usericon["name"], PATHINFO_EXTENSION);
        $targetFile = "../../images/user-icons/user-" . $userid . "-icon.png";
        
        move_uploaded_file($tempFile, $targetFile);
    }

    if (is_null($email) && is_null($username) && is_null($password)) {
        return;
    }

    $query = "UPDATE users SET";
    $params = "";
    $values = [];

    if (!is_null($email)) {
        $query .= " email = ?,";
        $params .= "s";
        $values[] = $email;
    }
    if (!is_null($username)) {
        $query .= " username = ?,";
        $params .= "s";
        $values[] = $username;
    }
    if (!is_null($password)) {
        $hashed_password = password_hash($password, PASSWORD_DEFAULT);

        $query .= " password = ?,";
        $params .= "s";
        $values[] = $hashed_password;
    }

    $query = rtrim($query, ",") . " WHERE userid = ?";
    $params .= "i";
    $values[] = $userid;
    $stmt = $conn->prepare($query);
    $stmt->bind_param($params, ...$values);
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
$usericon = $_FILES["usericon"] ?? NULL;
$email = $_POST["email"] ?? NULL;
$username = $_POST["username"] ?? NULL;
$password = $_POST["password"] ?? NULL;

if (!validId($userid)) {
    http_response_code(401);
    echo "Invalid UserID";
} elseif (!is_null($usericon) && $usericon['error'] !== UPLOAD_ERR_OK && !validImage($usericon)) {
    http_response_code(500);
    echo "Error: File upload error.";
    exit();
} elseif (!is_null($email) && !validEmail($email)) {
    http_response_code(400);
    echo "Invalid Email";
} elseif (!is_null($username) && !validUsername($username)) {
    http_response_code(400);
    echo "Invalid Username";
} elseif (!is_null($password) && !validPassword($password)) {
    http_response_code(400);
    echo "Invalid Password";
} else {
    modifyUser($conn, $userid, $usericon, $email, $username, $password);

    if (!is_null($username) && isset($_COOKIE["username"])) {
        setcookie("username", $username, time() + 86400, "/");
    }

    echo "User Modified";
}

$conn->close();

?>
