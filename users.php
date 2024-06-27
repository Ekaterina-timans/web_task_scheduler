<?php
require 'db_connection.php';
require 'jwt.php';

$headers = getallheaders();
$token = $headers['Authorization'];

if(isset($token)) {
    $tokenIssuer = new TokenIssuer();
    $tokenData = $tokenIssuer->validateToken($token);
    $user_id = $tokenData->id;
    $isAdmin = $tokenData->isAdmin;
    $isBanned = $tokenData->isBanned;
    if($user_id && $isAdmin == 1 && $isBanned == 0) {
        $sql = "SELECT users.*, IF(banned.user_id IS NULL, 0, 1) AS isBlocked 
                FROM users 
                LEFT JOIN banned ON users.id = banned.user_id";
        $result = mysqli_query($db_conn, $sql);
        if(mysqli_num_rows($result) > 0) {
            $users = array();

            while($row = mysqli_fetch_assoc($result)) {
                $user = array(
                    'id' => $row['id'],
                    'email' => $row['email'],
                    'dateRegistration' => $row['date_registration'],
                    'isBlocked' => $row['isBlocked']
                );
                $users[] = $user;
            }
            echo json_encode($users);
        } else {
            echo json_encode(array('message' => 'No users found.'));
        }
    }
    else {
        echo json_encode ('Invalid token');
    }
}
else {
    echo json_encode('Token is missing');
}
?>