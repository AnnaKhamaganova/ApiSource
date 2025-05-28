<?php

require_once 'SourseApi.php';

$apiUrl = "http://6d21d1646ba0.vps.myjino.ru/api/sources";

$apiClient = new SourceApi($apiUrl);

if ($argc < 2) {
    echo "Используйте команду: php index.php <команда> [аргументы]\n";
  exit(1);
}

$command = strtolower($argv[1]);

try {
  switch ($command) {
    case 'get':
      if ($argc != 3) {
        throw new Exception("Использование: php index.php get <page>\n");
      }
      $page = (int)$argv[2];
      $sources = $apiClient->getSources($page);
      echo "Ресурсы:\n";
      foreach ($sources["member"] as $source) {
        echo "Id: {$source['id']}, Название: {$source['name']}\n";
      }
      break;

    case 'post':
      if ($argc != 7) {
        throw new Exception("Использование: php index.php post <name> <description> <url> <attr1> <attr2>\n");
      }
      $name = $argv[2];
      $description = $argv[3];
      $sourceUrl = $argv[4];
      $attr1 = $argv[5];
      $attr2 = $argv[6];

      $response = $apiClient->addSource($name, $description, $sourceUrl, $attr1, $attr2);
      echo "Ресурс успешно добавлен с id {$response['id']}\n";
      break;

    case 'delete':
      if ($argc != 3) {
        throw new Exception("Использование: php index.php delete <id>\n");
      }
      $id = (int)$argv[2];
      echo $apiClient->deleteSource($id);
      break;

    case 'pages':
      if ($argc != 2) {
        throw new Exception("Использование: php index.php pages\n");
      }
      $sources = $apiClient->getSources(1);
      $lastpage = $sources["view"]["last"];
      $parts = parse_url($lastpage);
      if (isset($parts['query'])) {
        parse_str($parts['query'], $queryParams);
        $page = $queryParams['page'] ?? null;
        echo "Всего страниц: $page\n";
      } 
      break;

    case 'help':
    default:
      echo "Доступные команды:\n";
      echo " - get <page>\n";
      echo " - post <name> <description> <url> <attr1> <attr2>\n";
      echo " - delete <id>\n";
      echo " - pages\n";
      break;
  }
} catch (Exception $e) {
  echo "Ошибка: " . $e->getMessage() . "\n";
}
