<?php

function getConnection() {
    $con = getNewConnection();
    mysqli_select_db($con, "bluecard");
    return $con;
}

function getNewConnection() {
    $con = mysqli_connect("localhost", "root", "");

// Check connection
    if (mysqli_connect_errno($con)) {
        echo "Failed to connect to MySQL: " . mysqli_connect_error();
    }
    return $con;
}
//error_reporting(E_ALL ^ E_NOTICE); The server needs to ignore notices because the api needs to have null values in the logic.
define("DB_DSN", "mysql:host=localhost;dbname=bluecard"); //this constant will be use as our connectionstring/dsn
define("DB_USERNAME", "root"); //username of the database
define("DB_PASSWORD", ""); //password of the database

