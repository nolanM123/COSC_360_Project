<?php
session_start();

if ($_SERVER["REQUEST_METHOD"] !== "POST") {
    http_response_code(405);
    echo "Method Not Allowed";
    exit();
}

$_SESSION = array();
setcookie("userid", "", time() - 3600, "/");
setcookie("role", "", time() - 3600, "/");
setcookie("username", "", time() - 3600, "/");
echo "User Signed Out";

session_destroy();

?>