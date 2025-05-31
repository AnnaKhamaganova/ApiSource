<?php
require_once 'SourseApi.php';

// Получаем номер текущей страницы из GET-параметра, по умолчанию 1
$page = isset($_GET['page']) ? (int) $_GET['page'] : 1;

// Создаем API клиент
$apiUrl = 'http://6d21d1646ba0.vps.myjino.ru/api/sources';
$apiClient = new SourceApi($apiUrl);

// Получаем список ресурсов для текущей страницы
try {
    $sources = $apiClient->getSources($page);
} catch (Exception $e) {
    die('Ошибка при получении данных: ' . htmlspecialchars($e->getMessage()));
}

$lastpage = $sources["view"]["last"];
$parts = parse_url($lastpage);
if (isset($parts['query'])) {
    parse_str($parts['query'], $queryParams);
    $totalPages = $queryParams['page'] ?? null;
} 

?>

<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Список ресурсов</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            margin: 20px;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 20px;
        }
        th, td {
            border: 1px solid #ddd;
            padding: 8px;
        }
        th {
            background-color: #f2f2f2;
        }
        .lc{
            border: none
        }
        .delete-button {
            background-color: transparent; /* прозрачный фон */
            color: #f44336; /* цвет текста */
            border: none; /* убираем границу */
            padding: 5px 10px;
            cursor: pointer;
            font-size: 14px;
       }
       .delete-button:hover {
           text-decoration: underline; /* подчеркнуть при наведении */
        }
        /* Стиль для навигации */
        .pagination {
            text-align: center;
        }
        .pagination a {
            display: inline-block;
            padding: 8px 12px;
            margin: 0 4px;
            border: 1px solid #ccc;
            border-radius: 4px;
            text-decoration: none;
            color: #333;
        }
        .pagination a:hover {
            background-color: #f0f0f0;
        }
    </style>
    <script>
        function deleteResource(id) {
            if (confirm('Вы уверены, что хотите удалить этот ресурс?')) {
                fetch('delete.php?id=' + encodeURIComponent(id), {
                    method: 'GET' // Можно использовать GET или POST, в зависимости от реализации delete.php
                })
                .then(response => response.text())
                .then(data => {
                    alert(data);
                    location.reload(); // Обновляем страницу после удаления
                })
                .catch(error => {
                    console.error('Ошибка:', error);
                });
            }
        }
    </script>
</head>
<body>

<h1>Список ресурсов</h1>

<?php

echo '<table>';
echo '<thead><tr><th>ID</th><th>Название</th></tr></thead>';
echo '<tbody>';
foreach ($sources["member"] as $source) {
    echo '<tr>';
    echo '<td>' . $source['id']. '</td>';
    echo '<td>' . $source['name']. '</td>';
    echo '<td class="lc"><button class="delete-button" onclick="deleteResource(' . htmlspecialchars($source['id']) . ')">Удалить</button></td>';
    echo '</tr>';
}
echo '</tbody></table>';
echo '<div>'."\n".'</div>';

echo '<div class="pagination">';

if ($page > 1) {
    echo '<a href="?page=' . ($page - 1) . '">&laquo; Предыдущая</a>';
}

for ($p = 1; $p <= $totalPages; $p++) {
    if ($p == $page) {
        // Текущая страница — выделяем
        echo '<strong> ' . $p . ' </strong>';
    } else {
        echo '<a href="?page=' . $p . '">' . $p . '</a>';
    }
}

if ($page < $totalPages) {
    echo '<a href="?page=' . ($page + 1) . '">Следующая &raquo;</a>';
}

echo '</div>';

?>

</body>
</html>
