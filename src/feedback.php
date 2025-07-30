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

if ($action === "feedback"){

    $feedback = $conn->real_escape_string($data['feedback']);
    $rating = (int)$data['rating'];

    if($rating >= 1){
        $sql= "INSERT INTO Feedbacks (feedback, rating)
        VALUES ('$feedback', '$rating')";

        if ($conn->query($sql) === TRUE) { //lagyan ng identifier na nag ok
            echo json_encode(["success" => true]);
        }else {
        echo json_encode(["error" => "Error: " . $sql . "<br>" . $conn->error]);
        }

    }else{
        echo json_encode(["success" => false]);
    }
}elseif ($action === "getFeedbacks") {
     
    $sql = "SELECT feedback, rating FROM Feedbacks ORDER BY id DESC";
    $result = $conn->query($sql);
    
    if ($result) {
        $feedbacks = [];
        while ($row = $result->fetch_assoc()) {
            $feedbacks[] = [
                'username' => 'Anonymous User', // Default 
                'rating' => (int)$row['rating'],
                'comment' => $row['feedback'] 
            ];
        }
        echo json_encode(["success" => true, "feedbacks" => $feedbacks]);
    }else {
        echo json_encode(["success" => false, "error" => "Failed to fetch feedbacks"]);
    }

}else {
     echo json_encode(["success" => false, "message" => "Invalid action"]);
}