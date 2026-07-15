<?php

return [
    'db_host'    => getenv('DB_HOST') ?: 'localhost',
    'db_name'    => getenv('DB_NAME') ?: 'addaduma-wholesale_system',
    'db_user'    => getenv('DB_USER') ?: 'root',
    'db_pass'    => getenv('DB_PASS') ?: '',
    'db_charset' => 'utf8mb4',
];
