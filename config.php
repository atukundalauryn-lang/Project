<?php

// ==========================================
// SESSION
// ==========================================

session_start();


// ==========================================
// CURRENCY FUNCTION
// ==========================================

function getCurrency()
{
    if (isset($_SESSION['currency']) && !empty($_SESSION['currency'])) {
        return $_SESSION['currency'];
    }

return "";}


// ==========================================
// DATABASE CONFIGURATION
// ==========================================

function getDB()
{
    $host = "localhost";
    $dbname = "ims_db";
    $username = "root";
    $password = "";

    try {

        $db = new PDO(
            "mysql:host=$host;dbname=$dbname;charset=utf8",
            $username,
            $password
        );

        $db->setAttribute(
            PDO::ATTR_ERRMODE,
            PDO::ERRMODE_EXCEPTION
        );

        return $db;

    } catch (PDOException $e) {

        die(
            "Database Connection Failed: " .
            $e->getMessage()
        );

    }
}

?>