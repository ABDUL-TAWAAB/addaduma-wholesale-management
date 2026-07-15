<?php

function jsonResponse($data, int $status = 200): void{
    http_response_code($status);
    header('Content-Type: application/json');
    echo json_encode($data);
    exit;
}

function jsonError(string $message, int $status = 400): void{
    jsonResponse(['message' => $message], $status);
}

function getJsonBody(): array{
    $raw = file_get_contents('php://input');
    if (!$raw) {
        return [];
    }
    $data = json_decode($raw, true);
    return is_array($data) ? $data : [];
}

function getRequestMethod(): string{
    return strtoupper($_SERVER['REQUEST_METHOD']);
}

function getPathSegments(): array{
    $path = '';

    if (isset($_GET['path']) && is_string($_GET['path'])) {
        $path = trim($_GET['path'], '/');
    } else {
        $uri = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
        if (preg_match('#/api(?:/index\.php)?(.*)$#', $uri, $matches)) {
            $path = trim($matches[1], '/');
        }
    }

    return $path ? explode('/', $path) : [];
}

function castNumericFields(array $row, array $fields): array{
    foreach ($fields as $field) {
        if (isset($row[$field])) {
            $row[$field] = is_numeric($row[$field]) ? (strpos((string) $row[$field], '.') !== false ? (float) $row[$field] : (int) $row[$field]) : $row[$field];
        }
    }
    return $row;
}
