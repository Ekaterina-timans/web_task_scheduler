<?php
require 'db_connection.php';
require 'jwt.php';

$headers = getallheaders();
$token = $headers['Authorization'];

if(isset($token)) {
    $tokenIssuer = new TokenIssuer();
    $tokenData = $tokenIssuer->validateToken($token);
    $user_id = $tokenData->id;
    $isBanned = $tokenData->isBanned;
    if($user_id && $isBanned == 0) {
        if ($_SERVER['REQUEST_METHOD'] === 'GET') {
            $data = json_decode(file_get_contents("php://input"));
            if(isset($_GET["searchTerm"])) {
                $title = $_GET["searchTerm"];
                $task_title = mysqli_real_escape_string($db_conn, $title);
                $task_title_pattern = $task_title . "%";
        
                $sql = "SELECT * FROM Tasks WHERE user_id = '$user_id' AND title LIKE '$task_title_pattern'";
        
                $result = mysqli_query($db_conn, $sql);
                if(mysqli_num_rows($result) > 0) {
                    $rows = array();
                    while ($row = mysqli_fetch_assoc($result)) {
                        $rows[] = $row;
                    }
                    http_response_code(200);
                    echo json_encode($rows);
                } else {
                    http_response_code(404);
                    echo "Tasks not found";
                }
            } else {
                http_response_code(400);
                echo "Missing title parameter";
            }
        }
    }
    else {
        echo "Invalid token.";
    }
}
else {
    echo json_encode('Token is missing');
}
?>