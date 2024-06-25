<?php
require 'db_connection.php';
require 'jwt.php';

$data = json_decode(file_get_contents("php://input"));
if(isset($data->user_email) && isset($data->user_psw) && !empty(trim($data->user_email)) && !empty(trim($data->user_psw))) {

    $user_email = mysqli_real_escape_string($db_conn, trim($data->user_email));
    $user_psw = mysqli_real_escape_string($db_conn, trim($data->user_psw));
  
    $SQL = "SELECT users.*, banned.user_id AS banned_user_id FROM users 
            LEFT JOIN banned ON users.id = banned.user_id 
            WHERE email = '$user_email'";
    $result = mysqli_query($db_conn, $SQL);
    $user = mysqli_fetch_assoc($result);
    $tokenIssuer = new TokenIssuer();
    
    if ($user && password_verify($user_psw, $user['password'])) {
        
        if ($user['banned_user_id'] != null) {
            echo json_encode($user['banned_user_id']);
            http_response_code(401);
            echo json_encode(array('message' => 'Account is banned.'));
            return;
        }
        
        $tokenIssuer->createToken($user['id'], $user['is_admin'], $db_conn);
        echo json_encode(array('message' => 'User authenticated.', 'token' => $token));
    } else {
        echo json_encode('Authentication failed. Incorrect email or password.');
    }
} else {
    echo json_encode('Please provide email and password.');
    return;
}
?>