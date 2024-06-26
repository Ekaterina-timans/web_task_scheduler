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
        if($_SERVER['REQUEST_METHOD'] === 'POST') {
            if(isset($_FILES['file']) && isset($_GET['id'])) {
                $file = $_FILES['file'];
                $task_id = $_GET['id'];

                $upload_directory = './uploads/';
                $uploadFile = $upload_directory . basename($file['name']);

                if(move_uploaded_file($file['tmp_name'], $uploadFile)) {
                    $fileName = basename($file['name']);

                    $sql = "INSERT INTO files (file_name, task_id) VALUES (?, ?)";
                    $stmt = mysqli_prepare($db_conn, $sql);
                    mysqli_stmt_bind_param($stmt, 'si', $fileName, $task_id);
                    mysqli_stmt_execute($stmt);

                    if(mysqli_stmt_affected_rows($stmt) > 0) {
                        $response = array('message' => 'File uploaded successfully');
                    } else {
                        $response = array('message' => 'Failed to add file to the database');
                    }
                } else {
                    http_response_code(400);
                    $response = array('message' => 'Failed to upload file');
                }
                echo json_encode($response);
            } else {
                http_response_code(400);
                echo json_encode(array('message' => 'No file was sent'));
            }
        }
        elseif ($_SERVER['REQUEST_METHOD'] === 'GET' && isset($_GET['id'])) {
            $task_id = $_GET['id'];
            $sql = "SELECT f.id, f.file_name, t.id as task_id
                    FROM files f
                    INNER JOIN tasks t ON f.task_id = t.id
                    WHERE t.user_id = ? AND t.id = ?";
            $stmt = mysqli_prepare($db_conn, $sql);
            mysqli_stmt_bind_param($stmt, 'ii', $user_id, $task_id);
            mysqli_stmt_execute($stmt);
            $result = mysqli_stmt_get_result($stmt);
            if(mysqli_num_rows($result) > 0) {
                $files = array();
                while($row = mysqli_fetch_assoc($result)) {
                    $file = array(
                        'id' => $row['id'],
                        'task_id' => $row['task_id'],
                        'file_name' => $row['file_name'],
                        'download_url' => '/download.php?file_id=' . $row['id']
                    );
                    $files[] = $file;
                }
                echo json_encode($files);
            } else {
                echo json_encode(array('message' => 'No files found for the user tasks.'));
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