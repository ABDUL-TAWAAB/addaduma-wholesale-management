<?php

$uri = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);

if (preg_match('#^/api(?:/index\.php)?(/.*)?$#', $uri)) {
    require __DIR__ . '/api/index.php';
    return true;
}

return false;
