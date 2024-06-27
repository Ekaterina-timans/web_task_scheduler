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
        if($_SERVER['REQUEST_METHOD'] === 'PUT') {
            $data = json_decode(file_get_contents("php://input"));
            if(isset($_GET['id']) && isset($data->reasonBan) && isset($data->dataEnd)) {
                $id = $_GET['id'];
                $ban_id = mysqli_real_escape_string($db_conn, $id);
                $ban_reason = mysqli_real_escape_string($db_conn, $data->reasonBan);
                $ban_dataEnd = mysqli_real_escape_string($db_conn, $data->dataEnd);

                $sql = "UPDATE banned SET data_end = '$ban_dataEnd', reason = '$ban_reason' WHERE id = '$ban_id'";

                if(mysqli_query($db_conn, $sql)) {
                    http_response_code(200);
                    echo "User updated successfully.";
                } else {
                    http_response_code(400);
                    echo "Error updating user: " . mysqli_error($db_conn);
                }
            }
        }
        elseif ($_SERVER['REQUEST_METHOD'] === 'DELETE') {
            $data = json_decode(file_get_contents("php://input"));
            $id = $_GET['id'];
            $ban_id = mysqli_real_escape_string($db_conn, $id);
            $sql = "DELETE FROM banned WHERE id = $ban_id";
            if(mysqli_query($db_conn, $sql)) {
                http_response_code(200);
                echo "User deleted successfully.";
            } else {
                http_response_code(400);
                echo "Error deleting user: " . mysqli_error($db_conn);
            }
        }
        elseif ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $data = json_decode(file_get_contents("php://input"));
            if(isset($_GET['id']) && isset($data->reasonBan) && isset($data->dataEnd)) {
                $id = $_GET['id'];
                $ban_id = mysqli_real_escape_string($db_conn, $id);
                $ban_reason = mysqli_real_escape_string($db_conn, $data->reasonBan);
                $ban_dataEnd = mysqli_real_escape_string($db_conn, $data->dataEnd);
                $ban_dataStart = date('Y-m-d');
                
                $sql = "SELECT id FROM banned WHERE user_id='$ban_id'";
                $result = $db_conn->query($sql);

                if ($result->num_rows > 0) {
                    http_response_code(400);
                    echo json_encode(array("message" => "User already exists"));
                }
                else {
                    $sql = "INSERT INTO banned (user_id, data_start, data_end, reason) VALUES ('$ban_id', '$ban_dataStart', '$ban_dataEnd', '$ban_reason')";

                    if(mysqli_query($db_conn, $sql)) {
                        http_response_code(200);
                        echo "User is banned successfully.";
                    } else {
                        http_response_code(400);
                        echo "Error user locks: " . mysqli_error($db_conn);
                    }
                }
            }
            else {
                http_response_code(400);
                echo json_encode('Please fill all the required fields!');
                return;
            }
        }
        else {
            http_response_code(400);
            echo json_encode('User id is required!');
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