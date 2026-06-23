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
// ACTIVITY LOGGER
// ==========================================

function logActivity($db,$user,$action)
{

    $stmt=$db->prepare("

    INSERT INTO staff_activity
    (
        user_id,
        action
    )

    VALUES (?,?)

    ");


    $stmt->execute([

        $user,
        $action

    ]);

}







// ==========================================
// ADD SUPPLIER
// ==========================================


if(isset($_POST['add_supplier'])){


    $name = trim($_POST['name']);
    $phone = trim($_POST['phone']);
    $email = trim($_POST['email']);
    $address = trim($_POST['address']);




    if(empty($name)){


        $error="Supplier name is required.";


    }else{


        try{


            $stmt=$db->prepare("

            INSERT INTO suppliers

            (
                name,
                phone,
                email,
                address
            )

            VALUES (?,?,?,?)

            ");



            $stmt->execute([


                $name,
                $phone,
                $email,
                $address


            ]);






            logActivity(

                $db,

                $_SESSION['user_id'],

                "Added supplier ".$name

            );





            $msg="Supplier added successfully.";





        }catch(PDOException $e){


            $error="Could not save supplier.";

        }



    }



}








// ==========================================
// UPDATE SUPPLIER
// ==========================================


if(isset($_POST['update_supplier'])){


    $id=$_POST['id'];

    $name=$_POST['name'];
    $phone=$_POST['phone'];
    $email=$_POST['email'];
    $address=$_POST['address'];





    $stmt=$db->prepare("

    UPDATE suppliers SET

    name=?,

    phone=?,

    email=?,

    address=?


    WHERE id=?


    ");





    $stmt->execute([


        $name,

        $phone,

        $email,

        $address,

        $id


    ]);






    logActivity(

        $db,

        $_SESSION['user_id'],

        "Updated supplier ".$name

    );




    $msg="Supplier updated.";

}





// ==========================================
// DELETE SUPPLIER
// ==========================================


if(isset($_GET['delete'])){


    $id=$_GET['delete'];




    $stmt=$db->prepare("

    SELECT name

    FROM suppliers

    WHERE id=?

    ");


    $stmt->execute([$id]);


    $supplier=$stmt->fetch();







    try{


        $stmt=$db->prepare("

        DELETE FROM suppliers

        WHERE id=?

        ");



        $stmt->execute([$id]);





        logActivity(

            $db,

            $_SESSION['user_id'],

            "Deleted supplier ".$supplier['name']

        );




        $msg="Supplier deleted.";





    }catch(PDOException $e){


        $error="Supplier cannot be deleted because it is in use.";

    }



}








// ==========================================
// FETCH SUPPLIERS
// ==========================================


$suppliers=$db->query("

SELECT *

FROM suppliers

ORDER BY id DESC

")->fetchAll(PDO::FETCH_ASSOC);





?>






<!DOCTYPE html>

<html>


<head>


<title>Suppliers</title>


<style>


body{

font-family:Arial;

background:#f4f6f9;

padding:20px;

}



form,table{

background:white;

padding:20px;

border-radius:10px;

box-shadow:0 2px 8px #ccc;

width:100%;

}




input,textarea{

padding:10px;

margin:5px;

width:220px;

}




button{

background:#2563eb;

color:white;

padding:10px 20px;

border:0;

border-radius:5px;

}





table{

border-collapse:collapse;

margin-top:20px;

}



th,td{

padding:12px;

border-bottom:1px solid #ddd;

}



.success{

color:green;

}



.error{

color:red;

}



.delete{

color:red;

}



</style>



</head>





<body>




<h2>
Supplier Management
</h2>




<a href="dashboard.php">
← Dashboard
</a>





<p class="success">

<?=$msg?>

</p>



<p class="error">

<?=$error?>

</p>









<h3>
Add Supplier
</h3>





<form method="POST">



<input

name="name"

placeholder="Supplier Name"

required

>



<input

name="phone"

placeholder="Phone"

>



<input

name="email"

placeholder="Email"

>




<textarea

name="address"

placeholder="Address">

</textarea>





<button name="add_supplier">

Save Supplier

</button>





</form>









<h3>
Supplier List
</h3>





<table>



<tr>

<th>ID</th>

<th>Name</th>

<th>Phone</th>

<th>Email</th>

<th>Address</th>

<th>Action</th>


</tr>







<?php foreach($suppliers as $s): ?>



<tr>



<td>

<?=$s['id']?>

</td>



<td>

<?=$s['name']?>

</td>




<td>

<?=$s['phone']?>

</td>



<td>

<?=$s['email']?>

</td>



<td>

<?=$s['address']?>

</td>




<td>



<a class="delete"

href="?delete=<?=$s['id']?>"

onclick="return confirm('Delete supplier?')">

Delete

</a>



</td>





</tr>





<?php endforeach; ?>





</table>








</body>


</html>