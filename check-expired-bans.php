<?php
require 'db_connection.php';
$logFile = 'E:\OpenServer\OSPanel\domains\api\logs\check-expired-bans.log';
$current_date = date('Y-m-d');
$sql = "SELECT * FROM banned WHERE data_end < ?";
$stmt = $db_conn->prepare($sql);
$stmt->bind_param("s", $current_date);
$stmt->execute();
$result = $stmt->get_result();
$expired_users = $result->fetch_all(MYSQLI_ASSOC);

$logHandle = fopen($logFile, 'a');

foreach ($expired_users as $user) {
    $delete_sql = "DELETE FROM banned WHERE id = ?";
    $delete_stmt = $db_conn->prepare($delete_sql);
    $delete_stmt->bind_param("i", $user['id']);
    
    if ($delete_stmt->execute()) {
        $logMessage = "Пользователь с ID " . $user['id'] . " был удален.\n";
    } else {
        $logMessage = "Ошибка при удалении пользователя с ID " . $user['id'] . ".\n";
    }
    
    fwrite($logHandle, date('Y-m-d H:i:s') . ": " . $logMessage);
}

fclose($logHandle);
?>