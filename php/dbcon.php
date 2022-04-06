<?php

function MySQLCreateConn(){
    $servername = "localhost";
    $database = "premztgv_db";
    // superuser config
    $username = "premztgv_admin";
    $password = "G@tito69!";
    // insertonly config
    // $username = "premztgv_user";
    // $password = "premiosflorece2022";
    // Create connection using musqli_connect function
    $conn = mysqli_connect($servername, $username, $password, $database);
    // Connection Check
    if (!$conn) {
        // die("Connection failed: " . $conn->connect_error);
        echo "Connection failed: " . $conn->connect_error;

    }

    else{
        echo "Connected Successfully!";
        return $conn;
        // $conn->close();
    }
}
?>
