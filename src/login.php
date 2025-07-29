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

if( $action === "login") {

    $emaill=$data['email'];
    $passwordl=$data['password'];

    $sql = "SELECT * FROM Users WHERE Email = '$emaill';";
    $result = $conn->query($sql);

    if ($result && $result->num_rows > 0) {
        $dataLogin = $result->fetch_assoc();

        if(password_verify($passwordl,$dataLogin['Password'])){ //decrypt
                
            $tokenPayload=[
                'user_id' => $dataLogin['id'],
                'email' => $dataLogin['Email'],
                'iat' => time(),
                'exp' => time() + JWT_EXPIRE_TIME
            ];
                
            $token = JWT::encode($tokenPayload, JWT_SECRET_KEY, 'HS256');

            echo json_encode([ //ito yung meta data
                "success"=> true,
                "message" => "Logged In",
                "token"=> $token,
                "userData" => [
                    "ID" => $dataLogin['id'],
                    "email" => $dataLogin['Email'],
                     "reset" => $dataLogin['reset'],
                ]]);
            exit;
        }else{ //lagyan ng error
            echo json_encode(["success"=> false,"error" => "Password error"]);
        }
    } else {
        //error yan pag ganyan wag mo pilitin
        echo json_encode(["success"=> false,"error" => "Email not found"]);
    }

}elseif($action === 'signup'){

    $email=$data['email'];
    $password=$data['initpassword'];
    $conpassword=$data['conpassword'];

    if($password === $conpassword){

        $hash = password_hash($password, PASSWORD_DEFAULT); //hash

        $sql = "SELECT * FROM Users Where Email = '$email';";
        $result  = $conn->query($sql);

        //sign up
        
        if ($result && $result->num_rows > 0) {
            //meron nang email na gamit
            echo json_encode(["success"=> false,"error" => "Email already in use."]);
            exit;
        } else {

            $randomString = getRandomString(10);

            $sql1 = "INSERT INTO Users (Email, Password, reset)
            VALUES ('$email', '$hash', '$randomString')";

            if ($conn->query($sql1) === TRUE) { //lagyan ng identifier na nag ok
                echo json_encode(["success" => true, "message" => "New record created successfully"]);
            } else {
                echo json_encode(["error" => "Error: " . $sql1 . "<br>" . $conn->error]);
            }
        }
    }else{
        echo json_encode(["success" => false]);
    }

}else {
     echo json_encode(["success" => false, "message" => "Invalid action"]);
}