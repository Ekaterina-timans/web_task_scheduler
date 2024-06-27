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
        $sql = "SELECT b.*, u.email
            FROM banned b
            INNER JOIN users u ON b.user_id = u.id";
            
        $result = mysqli_query($db_conn, $sql);
        if(mysqli_num_rows($result) > 0) {
            $usersBanned = array();

            while($row = mysqli_fetch_assoc($result)) {
                $userBanned = array(
                    'id' => $row['id'],
                    'email' => $row['email'],
                    'dataStart' => $row['data_start'],
                    'dataEnd' => $row['data_end'],
                    'reasonBan' => $row['reason'],
                );
                $usersBanned[] = $userBanned;
            }

            echo json_encode($usersBanned);
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