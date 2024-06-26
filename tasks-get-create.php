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
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $data = json_decode(file_get_contents("php://input"));
            if(isset($data->title) && isset($data->description) && isset($data->priority)) {           
                
                $task_title = mysqli_real_escape_string($db_conn, $data->title);
                $task_descript = mysqli_real_escape_string($db_conn, $data->description);
                $task_status = 0;
                $task_priority = mysqli_real_escape_string($db_conn, $data->priority);
                $task_data_start = date('Y-m-d');

                if (isset($data->dataEnd)) {
                    $task_data_end = mysqli_real_escape_string($db_conn, $data->dataEnd);

                    $sql = "INSERT INTO Tasks (user_id, title, description, status, priority, data_start, data_end) VALUES ('$user_id', '$task_title', '$task_descript', '$task_status', '$task_priority', '$task_data_start', '$task_data_end')";
                }
                else {
                    $sql = "INSERT INTO Tasks (user_id, title, description, status, priority, data_start) VALUES ('$user_id', '$task_title', '$task_descript', '$task_status', '$task_priority', '$task_data_start')";
                }

                if(mysqli_query($db_conn, $sql)) {
                    http_response_code(200);
                    echo "Task created successfully.";
                } else {
                    http_response_code(400);
                    echo "Error creating task: " . mysqli_error($db_conn);
                }
            }
            else {
                http_response_code(400);
                echo json_encode('Please fill all the required fields!');
                return;
            }
        }
        else {
            $sql = "SELECT * FROM Tasks WHERE user_id = $user_id";
            $result = mysqli_query($db_conn, $sql);
            if(mysqli_num_rows($result) > 0) {
                $tasks = array();

                while($row = mysqli_fetch_assoc($result)) {
                    $task = array(
                        'id' => $row['id'],
                        'title' => $row['title'],
                        'description' => $row['description'],
                        'status' => $row['status'],
                        'priority' => $row['priority'],
                        'dataStart' => $row['data_start'],
                        'dataEnd' => $row['data_end']
                    );
                    $tasks[] = $task;
                }
    
                echo json_encode($tasks);
            } else {
                echo json_encode(array('message' => 'No tasks found for this user.'));
            }
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