<?php
    

    $host = "localhost";
    $user = "root";
    $pass = "";
    $database = "findpro";

    try {
        $conn = new PDO(
            "mysql:host=$host;dbname=$database;charset=utf8mb4",
            $user, $pass
        );
        
        $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    }catch (PDOException $e) {
        die("Connection failed: " . $e->getMessage());
    }
?>