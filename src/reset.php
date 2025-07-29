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

if ($action === 'reset'){

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
    
}else {
    echo json_encode(["success" => false, "message" => "Invalid action"]);
}