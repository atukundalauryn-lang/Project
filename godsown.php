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

$msg = "";
$error = "";




// ==========================================
// ADD GODOWN
// ==========================================

if(isset($_POST['add_godown'])){


    $name = trim($_POST['name']);
    $location = trim($_POST['location']);



    if(empty($name)){


        $error = "Godown name is required.";


    }else{


        try{


            $stmt = $db->prepare("

            INSERT INTO godowns

            (
            name,
            location
            )

            VALUES(?,?)

            ");



            $stmt->execute([

                $name,
                $location

            ]);



            $msg = "Godown added successfully.";



        }catch(PDOException $e){


            $error = "Could not add godown.";


        }


    }


}







// ==========================================
// DELETE GODOWN
// ==========================================

if(isset($_GET['delete'])){


    $id = $_GET['delete'];



    try{


        $stmt = $db->prepare("

        DELETE FROM godowns

        WHERE id=?

        ");



        $stmt->execute([$id]);



        $msg = "Godown deleted.";



    }catch(PDOException $e){


        $error = "Godown cannot be deleted because it is in use.";

    }



}






// ==========================================
// FETCH GODOWNS
// ==========================================


$godowns = $db->query("

SELECT *

FROM godowns

ORDER BY id DESC

")->fetchAll(PDO::FETCH_ASSOC);




?>







<!DOCTYPE html>

<html>


<head>


<title>Godown Management</title>


<style>


body{

font-family:Arial;

background:#f4f6f9;

}



.container{

padding:25px;

}



form,table{

background:white;

padding:20px;

border-radius:10px;

box-shadow:0 2px 8px #ccc;

}



input{

padding:10px;

margin:5px;

width:250px;

}




button{

background:#2563eb;

color:white;

border:0;

padding:10px 20px;

border-radius:5px;

cursor:pointer;

}



table{

width:100%;

border-collapse:collapse;

margin-top:20px;

}



th,td{

padding:12px;

border-bottom:1px solid #ddd;

}



.success{

background:#dcfce7;

padding:10px;

color:green;

}



.error{

background:#fee2e2;

padding:10px;

color:red;

}



.delete{

color:red;

}



</style>


</head>





<body>





<div class="container">






<h2>
Godown Management
</h2>




<a href="dashboard.php">

← Dashboard

</a>







<?php if($msg): ?>

<div class="success">

<?= $msg ?>

</div>

<?php endif; ?>







<?php if($error): ?>

<div class="error">

<?= $error ?>

</div>

<?php endif; ?>









<h3>
Add Godown
</h3>





<form method="POST">





<input

type="text"

name="name"

placeholder="Godown Name"

required

>






<input

type="text"

name="location"

placeholder="Location"

>







<button name="add_godown">

Save Godown

</button>






</form>









<h3>
Godown List
</h3>







<table>



<tr>

<th>ID</th>

<th>Name</th>

<th>Location</th>

<th>Action</th>


</tr>






<?php foreach($godowns as $g): ?>




<tr>



<td>

<?= $g['id'] ?>

</td>





<td>

<?= $g['name'] ?>

</td>





<td>

<?= $g['location'] ?>

</td>







<td>


<a class="delete"

href="?delete=<?= $g['id'] ?>"

onclick="return confirm('Delete this godown?')">


Delete


</a>



</td>





</tr>





<?php endforeach; ?>







</table>







</div>






</body>


</html>