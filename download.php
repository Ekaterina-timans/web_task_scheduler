<?php
require 'db_connection.php';

if ($_SERVER['REQUEST_METHOD'] === 'GET' && isset($_GET['file_id'])) {
    $file_id = $_GET['file_id'];

    $sql = "SELECT file_name FROM files WHERE id = ?";
    $stmt = mysqli_prepare($db_conn, $sql);
    mysqli_stmt_bind_param($stmt, 'i', $file_id);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);

    if ($row = mysqli_fetch_assoc($result)) {
        $fileName = $row['file_name'];
        $filePath = './uploads/' . $fileName;

        if (file_exists($filePath)) {

            header('Content-Description: File Transfer');
            header('Content-Type: application/octet-stream');
            header('Content-Disposition: attachment; filename="' . basename($fileName) . '"');
            header('Expires: 0');
            header('Cache-Control: must-revalidate');
            header('Pragma: public');
            header('Content-Length: ' . filesize($filePath));
            flush();
            readfile($filePath);
            exit;
        } else {
            echo json_encode(array('message' => 'File not found.'));
        }
    } else {
        echo json_encode(array('message' => 'No file found with the provided ID.'));
    }
} else {
    echo json_encode(array('message' => 'Invalid request.'));
}
?>