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
    if($user_id && $isBanned == 0 && isset($_GET['id'])) {
        if($_SERVER['REQUEST_METHOD'] === 'PUT')
        {
            $data = json_decode(file_get_contents("php://input"));
            $id = $_GET['id'];
            if(isset($data->title) && isset($data->description) && isset($data->priority)) {
                $task_id = mysqli_real_escape_string($db_conn, $id);
                $task_title = mysqli_real_escape_string($db_conn, $data->title);
                $task_descript = mysqli_real_escape_string($db_conn, $data->description);
                $task_priority = mysqli_real_escape_string($db_conn, $data->priority);

                if (isset($data->dataEnd)) {
                    $task_data_end = mysqli_real_escape_string($db_conn, $data->dataEnd);

                    $sql = "UPDATE Tasks SET title = '$task_title', description = '$task_descript', priority = '$task_priority', data_end = '$task_data_end' WHERE id = $task_id AND user_id = $user_id";
                }
                else {
                    $sql = "UPDATE Tasks SET title = '$task_title', description = '$task_descript', priority = '$task_priority' WHERE id = $task_id AND user_id = $user_id";
                }

                if(mysqli_query($db_conn, $sql)) {
                    http_response_code(200);
                    echo "Task updated successfully.";
                } else {
                    http_response_code(400);
                    echo "Error updating task: " . mysqli_error($db_conn);
                }
            }
            elseif (isset($data->status)) {
                $task_id = mysqli_real_escape_string($db_conn, $id);
                $task_status = ($data->status) ? 1 : 0;
                
                $sql = "UPDATE Tasks SET status = '$task_status' WHERE id = $task_id AND user_id = $user_id";
                if(mysqli_query($db_conn, $sql)) {
                    http_response_code(200);
                    echo "Task updated successfully.";
                } else {
                    http_response_code(400);
                    echo "Error updating task: " . mysqli_error($db_conn);
                }
            }
            else {
                http_response_code(400);
                echo json_encode('Please fill all the required fields!');
            }
        }
        elseif ($_SERVER['REQUEST_METHOD'] === 'DELETE') {
            $data = json_decode(file_get_contents("php://input"));
            $id = $_GET['id'];
            $task_id = mysqli_real_escape_string($db_conn, $id);
            $sql = "DELETE FROM Tasks WHERE id = $task_id AND user_id = $user_id";
            if(mysqli_query($db_conn, $sql)) {
                http_response_code(200);
                echo "Task deleted successfully.";
            } else {
                http_response_code(400);
                echo "Error deleting task: " . mysqli_error($db_conn);
            }
        }
        else {
            http_response_code(400);
            echo json_encode('Task id is required!');
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