<?php

require_once 'includes/config.php';


// ==========================================
// AUTHENTICATION
// ==========================================

if (!isset($_SESSION['user_id'])) {

    header("Location: login.php");
    exit;

}



$db = getDB();



// ==========================================
// ADMIN ONLY
// ==========================================

if ($_SESSION['role'] !== 'admin') {

    die("Access denied. Admin only.");

}



// ==========================================
// RECORD PAGE VISIT
// ==========================================


$stmt = $db->prepare("

INSERT INTO staff_activity

(user_id, action)

VALUES (?,?)

");


$stmt->execute([

    $_SESSION['user_id'],

    "Opened staff activity page"

]);







// ==========================================
// FETCH ACTIVITIES
// ==========================================


$activities = $db->query("

SELECT

a.id,

u.fullname,

u.username,

u.role,

a.action,

a.activity_time


FROM staff_activity a


LEFT JOIN users u

ON a.user_id=u.id


ORDER BY a.id DESC


")->fetchAll(PDO::FETCH_ASSOC);



?>





<!DOCTYPE html>

<html>


<head>


<title>Staff Activity</title>



<style>


body{

font-family:Arial;

background:#f4f6f9;

}



.container{

padding:25px;

}



table{

width:100%;

background:white;

border-collapse:collapse;

margin-top:20px;

box-shadow:0 2px 8px #ccc;

}



th,td{

padding:12px;

border-bottom:1px solid #ddd;

}



a{

text-decoration:none;

}



</style>



</head>





<body>



<div class="container">





<h2>
Staff Activity Log
</h2>




<a href="dashboard.php">

← Dashboard

</a>








<table>




<tr>


<th>ID</th>

<th>Staff</th>

<th>Username</th>

<th>Role</th>

<th>Action</th>

<th>Date & Time</th>



</tr>






<?php foreach($activities as $a): ?>



<tr>



<td>

<?= $a['id'] ?>

</td>




<td>

<?= $a['fullname'] ?>

</td>




<td>

<?= $a['username'] ?>

</td>





<td>

<?= $a['role'] ?>

</td>





<td>

<?= $a['action'] ?>

</td>





<td>

<?= $a['activity_time'] ?>

</td>





</tr>





<?php endforeach; ?>






</table>







</div>






</body>


</html>