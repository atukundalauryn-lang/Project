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
// ADMIN ONLY
// ==========================================

if ($_SESSION['role'] !== 'admin') {

    die("Access denied. Admin only.");

}





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
// ADD USER
// ==========================================


if(isset($_POST['add_user'])){


    $fullname = trim($_POST['fullname']);
    $username = trim($_POST['username']);
    $email    = trim($_POST['email']);
    $password = $_POST['password'];
    $role     = $_POST['role'];




    if(empty($fullname) || empty($username) || empty($password)){


        $error="Fill all required fields.";


    }else{



        try{


            $hashedPassword = password_hash(

                $password,

                PASSWORD_DEFAULT

            );





            $stmt=$db->prepare("

            INSERT INTO users

            (
                fullname,
                username,
                email,
                password,
                role,
                status
            )

            VALUES (?,?,?,?,?,?)

            ");





            $stmt->execute([


                $fullname,

                $username,

                $email,

                $hashedPassword,

                $role,

                'active'


            ]);








            logActivity(

                $db,

                $_SESSION['user_id'],

                "Created user ".$username

            );





            $msg="User created successfully.";







        }catch(PDOException $e){


            $error="Username already exists.";

        }



    }



}









// ==========================================
// UPDATE USER
// ==========================================


if(isset($_POST['update_user'])){


    $id=$_POST['id'];

    $fullname=$_POST['fullname'];

    $email=$_POST['email'];

    $role=$_POST['role'];

    $status=$_POST['status'];





    $stmt=$db->prepare("

    UPDATE users SET

    fullname=?,

    email=?,

    role=?,

    status=?


    WHERE id=?

    ");




    $stmt->execute([


        $fullname,

        $email,

        $role,

        $status,

        $id


    ]);






    logActivity(

        $db,

        $_SESSION['user_id'],

        "Updated user ID ".$id

    );





    $msg="User updated.";

}





// ==========================================
// DELETE USER
// ==========================================


if(isset($_GET['delete'])){


    $id=$_GET['delete'];



    if($id == $_SESSION['user_id']){


        $error="You cannot delete yourself.";



    }else{



        $stmt=$db->prepare("

        SELECT username

        FROM users

        WHERE id=?

        ");


        $stmt->execute([$id]);


        $user=$stmt->fetch();






        $stmt=$db->prepare("

        DELETE FROM users

        WHERE id=?

        ");



        $stmt->execute([$id]);







        logActivity(

            $db,

            $_SESSION['user_id'],

            "Deleted user ".$user['username']

        );






        $msg="User deleted.";

    }



}








// ==========================================
// FETCH USERS
// ==========================================


$users=$db->query("

SELECT *

FROM users

ORDER BY id DESC

")->fetchAll(PDO::FETCH_ASSOC);



?>






<!DOCTYPE html>

<html>


<head>


<title>User Management</title>


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





input,select{

padding:10px;

margin:5px;

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
User Management
</h2>




<a href="dashboard.php">

← Dashboard

</a>







<p class="success">

<?= $msg ?>

</p>




<p class="error">

<?= $error ?>

</p>









<h3>
Create User
</h3>







<form method="POST">





<input

name="fullname"

placeholder="Full Name"

required

>




<input

name="username"

placeholder="Username"

required

>




<input

name="email"

placeholder="Email"

>




<input

type="password"

name="password"

placeholder="Password"

required

>






<select name="role">


<option value="admin">
Admin
</option>


<option value="manager">
Manager
</option>


<option value="staff">
Staff
</option>


</select>






<button name="add_user">

Create User

</button>






</form>








<h3>
System Users
</h3>







<table>



<tr>


<th>ID</th>

<th>Name</th>

<th>Username</th>

<th>Email</th>

<th>Role</th>

<th>Status</th>

<th>Action</th>



</tr>







<?php foreach($users as $u): ?>



<tr>



<td>

<?=$u['id']?>

</td>




<td>

<?=$u['fullname']?>

</td>




<td>

<?=$u['username']?>

</td>




<td>

<?=$u['email']?>

</td>




<td>

<?=$u['role']?>

</td>




<td>

<?=$u['status']?>

</td>





<td>



<a class="delete"

href="?delete=<?=$u['id']?>"

onclick="return confirm('Delete user?')">


Delete


</a>



</td>





</tr>






<?php endforeach; ?>






</table>






</body>


</html>