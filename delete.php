<?php
require_once 'SourseApi.php';

if (!isset($_GET['id'])) {
    die('Не указан ID ресурса.');
}

$id = (int) $_GET['id'];
$apiUrl = 'http://6d21d1646ba0.vps.myjino.ru/api/sources';
$apiClient = new SourceApi($apiUrl);

try {
    $result = $apiClient->deleteSource($id);
    echo "Ресурс успешно удален.";
} catch (Exception $e) {
    echo "Ошибка при удалении: " . htmlspecialchars($e->getMessage());
}
?>