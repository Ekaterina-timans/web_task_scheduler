<?php
require 'db_connection.php';
require 'jwt.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $data = json_decode(file_get_contents("php://input"));

    if(isset($data->user_email) && isset($data->user_psw) && !empty(trim($data->user_email)) && !empty(trim($data->user_psw))) {
        $user_email = mysqli_real_escape_string($db_conn, trim($data->user_email));
        $user_psw = mysqli_real_escape_string($db_conn, trim($data->user_psw));

        $sql = "SELECT id FROM users WHERE email='$user_email'";
        $result = $db_conn->query($sql);

        if ($result->num_rows > 0) {
            http_response_code(400);
            echo json_encode(array("message" => "Username already exists"));
        }
        else {
            $hashed_password = password_hash($user_psw, PASSWORD_DEFAULT);
            $isAdmin = 0;
            $user_date_registration = date('Y-m-d');

            $add = mysqli_query($db_conn,"insert into users (email, password, is_admin, date_registration) values('$user_email', '$hashed_password', '$isAdmin', '$user_date_registration')");
            $tokenIssuer = new TokenIssuer();

            if($add) {
                $userId = mysqli_insert_id($db_conn);
                $tokenIssuer->createToken($userId, $isAdmin, $db_conn);
                http_response_code(200);
                echo json_encode(array('message' => 'User created.', 'token' => $token));
            }
            else {
                echo json_encode('Failed to create user.');
            }
        }
    }
    else {
        http_response_code(400);
        echo json_encode('Please fill all the required fields!');
        return;
    }
}
?>