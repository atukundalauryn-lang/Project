<?php

require_once 'includes/config.php';


// ==========================================
// AUTHENTICATION
// ==========================================

if (!isset($_SESSION['user_id'])) {

    header("Location: login.php");
    exit;

}



// ==========================================
// ADMIN ONLY
// ==========================================

if ($_SESSION['role'] !== 'admin') {

    die("Access denied. Admin only.");

}



$db = getDB();


function logActivity($db, $user_id, $action)
{
    $stmt = $db->prepare("
        INSERT INTO staff_activity
        (
            user_id,
            action
        )
        VALUES (?,?)
    ");

    $stmt->execute([
        $user_id,
        $action
    ]);
}


// ==========================================
// CREATE DATABASE BACKUP
// ==========================================


if(isset($_POST['backup'])){


    $filename = "ims_backup_" . date("Y-m-d_H-i-s") . ".sql";
    logActivity(
   	 $db,
   	 $_SESSION['user_id'],
   	 "Created database backup: ".$filename
);


    header('Content-Type: application/sql');

    header(
        'Content-Disposition: attachment; filename="' . $filename . '"'
    );



    echo "-- =====================================\n";
    echo "-- INVENTORY MANAGEMENT SYSTEM BACKUP\n";
    echo "-- Date: ".date("Y-m-d H:i:s")."\n";
    echo "-- =====================================\n\n";


    echo "CREATE DATABASE IF NOT EXISTS ims_db;\n";
    echo "USE ims_db;\n\n";




    // Get tables


    $tables = $db->query("SHOW TABLES")
                 ->fetchAll(PDO::FETCH_COLUMN);




    foreach($tables as $table){



        // Skip views handled separately

        echo "\n\n-- ==========================\n";
        echo "-- TABLE: $table\n";
        echo "-- ==========================\n\n";



        // Structure


        $create = $db->query(
            "SHOW CREATE TABLE `$table`"
        )->fetch(PDO::FETCH_ASSOC);



        echo "DROP TABLE IF EXISTS `$table`;\n\n";

        echo $create['Create Table'].";\n\n";





        // Data


        $rows = $db->query(
            "SELECT * FROM `$table`"
        );




        while($row = $rows->fetch(PDO::FETCH_ASSOC)){


            $columns = array_map(function($col){

                return "`$col`";

            }, array_keys($row));



            $values = array_map(function($value) use ($db){


                if($value === null){

                    return "NULL";

                }


                return $db->quote($value);



            }, array_values($row));





            echo "INSERT INTO `$table` (" .
            implode(",", $columns) .
            ") VALUES (" .
            implode(",", $values) .
            ");\n";


        }



        echo "\n";

    }



    exit;


}



?>





<!DOCTYPE html>

<html>


<head>


<title>Database Backup</title>


<style>


body{

font-family:Arial;

background:#f4f6f9;

}



.container{

padding:30px;

}



.box{

background:white;

padding:30px;

border-radius:10px;

box-shadow:0 2px 8px #ccc;

width:400px;

}



button{

background:#2563eb;

color:white;

border:0;

padding:12px 20px;

border-radius:5px;

cursor:pointer;

}



a{

text-decoration:none;

}



</style>


</head>





<body>



<div class="container">



<div class="box">



<h2>
Database Backup
</h2>



<p>
Click the button below to download a complete backup of your inventory database.
</p>




<form method="POST">



<button name="backup">

Create Backup

</button>



</form>





<br>



<a href="dashboard.php">

← Back to Dashboard

</a>



</div>



</div>



</body>


</html>