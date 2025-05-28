<?php

class SourceApi {
    private $baseUrl;

    public function __construct(string $baseUrl) {
        $this->baseUrl = rtrim($baseUrl, '/');
    }

    /* Получить список источников по странице */
    public function getSources(int $page): array {
        $url = "{$this->baseUrl}?page=$page";

        $response = $this->sendRequest($url, 'GET');

        $sources = json_decode($response, true);

        if ($sources === null || $sources === false) {
            throw new Exception("Ошибка декодирования JSON");
        }

        if (!isset($sources["member"])) {
            throw new Exception("Ресурсы не найдены");
        }

        return $sources;
    }

    /* Добавить новый источник*/
    public function addSource(string $name, string $description, string $url, string $attr1, string $attr2): array {
        $data = [
            "name" => $name,
            "description" => $description,
            "url" => $url,
            "attr1" => $attr1,
            "attr2" => $attr2,
            "platform" => "/api/platforms/1",
        ];

        return json_decode(
            $this->sendRequest($this->baseUrl, 'POST', json_encode($data)),
            true
        );
    }

    /* Удалить источник по ID */
    public function deleteSource(int $id): string {
        $url = "{$this->baseUrl}/$id";

        return $this->sendRequest($url, 'DELETE');
    }

    /* Вспомогательный метод для отправки HTTP-запросов */
    private function sendRequest(string $url, string $method, ?string $body = null): string {
        $ch = curl_init($url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

        switch (strtoupper($method)) {
            case 'POST':
                curl_setopt($ch, CURLOPT_POST, true);
                if ($body !== null) {
                    curl_setopt($ch, CURLOPT_POSTFIELDS, $body);
                }
                break;
            case 'DELETE':
                curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'DELETE');
                break;
            case 'GET':
                // по умолчанию GET
                break;
            default:
                throw new Exception("Неизвестный метод: {$method}");
        }

        // Заголовки
        if ($method === 'POST' || !empty($body)) {
            curl_setopt($ch, CURLOPT_HTTPHEADER, [
                'Accept: application/ld+json',
                'Content-Type: application/ld+json; charset=utf-8',
            ]);
        }

        // Выполнение запроса
        $response = curl_exec($ch);
        if (curl_errno($ch)) {
            throw new Exception('Ошибка cURL: ' . curl_error($ch));
        }
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);

        // Проверка кода ответа
        switch ($httpCode) {
            case 200:
            case 201:
            case 204:
                // Всё хорошо
                return ($method === 'DELETE') ? "Ресурс успешно удален.\n" : $response;
            case 400:
                throw new Exception('Введены некорректные данные');
            case 404:
                throw new Exception('Ресурс с таким ID не найден.');
            default:
                throw new Exception('Получен неожиданный код ответа: '. $httpCode);
        }
    }
}