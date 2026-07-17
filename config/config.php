<?php
// CONFIGURATION FILE THAT CAPTURE INFORMATION ABOUT THE DATABASE ON OUR LOCAL MACHINE
// THIS HELP US TO CONNECT TO THE DATABASE SO WE CAN SEND AND RECIEVE DATA FROM THE DATABASE
return [
    'db_host'    => getenv('DB_HOST') ?: 'localhost',
    'db_name'    => getenv('DB_NAME') ?: 'addaduma-wholesale_system',
    'db_user'    => getenv('DB_USER') ?: 'root',
    'db_pass'    => getenv('DB_PASS') ?: '',
    'db_charset' => 'utf8mb4',
];
