<?php

ini_set('display_errors', 1);
error_reporting(E_ALL);

session_start();

header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Credentials: true');
header("Access-Control-Allow-Methods: GET, POST, OPTIONS");
header('Access-Control-Allow-Headers: Content-Type, Authorization');
header('Access-Control-Max-Age: 86400');
header('Content-Type: application/json');

$action = '';
$data = [];

use Firebase\JWT\JWT;
use Firebase\JWT\Key;

define('JWT_SECRET_KEY', 'market');
define('JWT_EXPIRE_TIME', 3600);

require 'vendor/autoload.php';

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit();
}

if ($_SERVER['REQUEST_METHOD'] !== 'OPTIONS') {
    header('Content-Type: application/json');
}

$host ="";
$user ="";
$password="";
$dbname="";

$conn = new mysqli($host, $user, $password, $dbname);

if($conn -> connect_error) {
    die("Connection Failed" . $conn -> connect_error);
}

if($_SERVER['REQUEST_METHOD'] === 'POST'){
    $data = json_decode(file_get_contents('php://input'), true);

    // Check if data is structured with feedbackData property
    if (isset($data['feedbackData'])) {
        $data = $data['feedbackData'];
    }

    else if (isset($data['resetData'])){
        $data = $data['resetData'];
    }

    else if (isset($data['feedback']) || isset($data['rating'])) {

    }

    $action = isset($data['action']) ? $data['action'] : '';

    error_log("Received action: " . $action);
    error_log("Received data: " . json_encode($data));
}

function verifyToken() {
    $headers = getallheaders();
    if (!isset($headers['Authorization'])) {
        return false;
    }

    $authHeader = $headers['Authorization'];
    $token = str_replace('Bearer ', '', $authHeader);

    try {
        $decoded = JWT::decode($token, new Key(JWT_SECRET_KEY, 'HS256'));
        return $decoded;
    } catch (Exception $e) {
        return false;
    }
}

if ($action === 'getUsers'){
    $sql = "SELECT Email, created_at FROM Users ORDER BY id DESC";
    $result = $conn->query($sql);
    
    if ($result) {
        $users = [];
        while ($row = $result->fetch_assoc()) {
            $users[] = [
                'email' => $row['Email'], 
                'created' => $row['created_at'], 
            ];
        }
        echo json_encode(["success" => true, "users" => $users]);
    }else {
        echo json_encode(["success" => false, "error" => "Failed to fetch users"]);
    }

}elseif ($action === 'deleteUsers'){

    $postData = json_decode(file_get_contents('php://input'), true);
    $User = $conn->real_escape_string($postData['User']);

    $sql1 = "SELECT Email FROM Users WHERE Email = '$User'";
    $result = $conn->query($sql1);

    if ($result && $result->num_rows > 0) {

        $sql2 = "DELETE FROM Users WHERE Email = '$User'";

        if ($conn->query($sql2)) {
            echo json_encode(["success" => true, "message" => "User deleted successfully"]);
        } else {
            throw new Exception("Deleted failed: " . $conn->error);
        }
    } else {
        echo json_encode(["success" => false, "error" => "User does not exist"]);
    }

}else{
    echo json_encode(["success" => false, "message" => "Invalid action"]);
}