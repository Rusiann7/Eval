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

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\SMTP;
use PHPMailer\PHPMailer\Exception;
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

$action = isset($data['action']) ? $data['action'] : '';

$host = "localhost";
$user = "root";
$password = "";
$dbname = "Evaluation";

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

$n = 10;
function getRandomString($n)
{
    $characters = '0123456789ABCDEFGHIJKLMNOPQRSTUVWXYZ';
    $randomString = '';

    for ($i = 0; $i < $n; $i++) {
        $index = rand(0, strlen($characters) - 1);
        $randomString .= $characters[$index];
    }
    return $randomString;
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

}elseif ($action === 'reset'){

    global $epass;
        //get the incoming email
    $email = $data['email'];

    $sql = "SELECT * FROM Users WHERE Email = '$email';";
    $result = $conn->query($sql);

    if ($result && $result->num_rows > 0) {
        $dataReset = $result->fetch_assoc();
        $reset = $dataReset['reset'];

        $mail = new PHPMailer(true);

        //Server settings
        $mail->SMTPDebug = 0;                      //Enable verbose debug output
        $mail->isSMTP();                                            //Send using SMTP
        $mail->Host       = 'smtp.gmail.com';                     //Set the SMTP server to send through
        $mail->SMTPAuth   = true;                                   //Enable SMTP authentication
        $mail->Username   = 'systemmailer678@gmail.com';                     //SMTP username
        $mail->Password   = $epass;                               //SMTP password
        $mail->SMTPSecure = PHPMailer::ENCRYPTION_SMTPS;            //Enable implicit TLS encryption
        $mail->Port       = 465;                                    //TCP port to connect to; use 587 if you have set `SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS`
        $mail->addEmbeddedImage('Logo.png', 'logo','Logo.png');

        //Recipients
        $mail->setFrom('systemmailer678@gmail.com', 'Market AutoMailer');
        $mail->addAddress($email);     //Add a recipient
        $mail->addAddress($email);     //Name is optional
        $mail->addReplyTo('systemmailer678@gmail.com', 'Information');
        $mail->addCC('systemmailer678@gmail.com');
        $mail->addBCC('systemmailer678@gmail.com');

        //Attachments
        //$mail->addAttachment('/var/tmp/file.tar.gz');         //Add attachments
        //$mail->addAttachment('/tmp/image.jpg', 'new.jpg');    //Optional name

        //Content
        $mail->isHTML(true);                                  //Set email format to HTML
        $mail->Subject = 'Password Reset';
        $mail->Body    =
        '
        <br>
        <center>
        <img src="cid:logo" alt="Logo" style="max-width: 350px; width: 100%; height: auto;"> 
        <br>
        <h2>Enter this code to reset your password<h2>
        <h1>' . htmlspecialchars($reset, ENT_QUOTES, 'UTF-8') . '</h1>
        <br>
        <p>Do not share with anyone</p>
        </center>
        <br>
        ';

        $mail->AltBody = 'Enter this code to reset your password: ' .$reset;

        if($mail->send()){
            //echo msg has been sent
            echo json_encode(["success" => true,]);
        }
        else{
            echo json_encode(["success" => false]);
        }

    }else{
        //echo error no email found
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

}elseif($action === 'code'){
    $email = $data['email'];
    $code = $data['code'];

    $sql = "SELECT * FROM Users WHERE Email = '$email' AND reset = '$code';";
    $result = $conn->query($sql);

    if ($result && $result->num_rows > 0) {
        echo json_encode(["success" => true,]);
    } else{
        echo json_encode(["success" => false]);
    }

} elseif ($action === 'password'){
    $email = $data['email'];
    $password = $data['initPassword'];
    $conpassword = $data['conPassword'];

       if($password === $conpassword){

           $hash = password_hash($password, PASSWORD_DEFAULT); //hash
           $newReset = getRandomString(10);

           $sql="UPDATE Users
           SET Password = '$hash', reset = '$newReset'
           WHERE Email = '$email'
           ";

           $result = $conn->query($sql);

           if ($conn->query($sql) === TRUE) {
               echo json_encode(["success" => true]);
           } else{
               echo json_encode(["success" => false]);
           }

       } else{
        echo json_encode(["success" => false]);
       }
    
} elseif( $action === "login") {

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

}elseif ($action === 'getUsers'){
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

}