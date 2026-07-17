<?php
// THIS FILE DOES THE ACTUAL CONNECTION OF THE DATABASE
// IT ALSO CONTAINS FUNCTIONS AND WILL BE CALLED TO EXECUTE CODE AND ALSO
// THROW ERRORS IF CONDITIONS ARE NOT MET.
// THEREFORE WE IMPORT THE CONFICURATION FILE TO AND ENABLE THE ACTUAL CONNECTIONS




//Function that connects the database and check its health
function db_connect(){
    static $connection = null;
    if ($connection !== null) {
        return $connection;
    }

    $config = require __DIR__ . '/config.php';
    mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);

    $connection = mysqli_connect($config['db_host'], $config['db_user'], $config['db_pass'], $config['db_name']);
    if ($connection === false) {
        throw new RuntimeException('Database connection failed: ' . mysqli_connect_error());
    }
    if (!mysqli_set_charset($connection, $config['db_charset'])) {
        throw new RuntimeException('Failed to set charset: ' . mysqli_error($connection));
    }
    return $connection;
}


//function and capture the response of the database connection
function getDB(){
    return db_connect();
}

// function that execute or run a query which takes to parameters, the database connection and 
// the query to be executed and store the answer in a variable called results
function db_query($connection, string $sql){
    $result = mysqli_query($connection, $sql);
    if($result === false){
        throw new RuntimeException(mysqli_error($connection));
    }
    return $result;
}


// 
function db_prepare($connection, string $sql){
    $statement = mysqli_prepare($connection, $sql);
    if ($statement === false) {
        throw new RuntimeException(mysqli_error($connection));
    }
    return $statement;
}

function db_execute($statement, array $params = []): bool{
    if ($params !== []) {
        $types = '';
        foreach ($params as $param) {
            $types .= getParamType($param);
        }

        $bindParams = [$types];
        foreach ($params as $key => $value) {
            $bindParams[] = &$params[$key];
        }

        if (!mysqli_stmt_bind_param($statement, ...$bindParams)) {
            throw new RuntimeException(mysqli_stmt_error($statement));
        }
    }

    if (!mysqli_stmt_execute($statement)) {
        throw new RuntimeException(mysqli_stmt_error($statement));
    }

    return true;
}

function getParamType($param): string
{
    if ($param === null) {
        return 's';
    }

    if (is_int($param)) {
        return 'i';
    }

    if (is_float($param)) {
        return 'd';
    }

    return 's';
}

function getDbResult($resource)
{
    if ($resource instanceof mysqli_stmt) {
        return mysqli_stmt_get_result($resource);
    }

    if ($resource instanceof mysqli_result) {
        return $resource;
    }
    return false;
}

// funtions that fetch associative results
function db_fetch_assoc($resource){
    $result = getDbResult($resource);
    if ($result === false) {
        return false;
    }
    return mysqli_fetch_assoc($result);
}

function db_fetch_all($resource): array{
    $result = getDbResult($resource);
    if ($result === false) {
        return [];
    }

    $rows = [];
    while ($row = mysqli_fetch_assoc($result)) {
        $rows[] = $row;
    }

    return $rows;
}

function db_fetch_column($resource){
    $result = getDbResult($resource);
    if ($result === false) {
        return false;
    }

    $row = mysqli_fetch_row($result);
    if ($row === null) {
        return false;
    }

    return $row[0];
}

function db_last_insert_id($connection): int{
    return (int) mysqli_insert_id($connection);
}
