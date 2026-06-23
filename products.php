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
// ADD PRODUCT
// ==========================================

if(isset($_POST['add_product'])){


    $product_name  = trim($_POST['product_name']);
    $product_code  = trim($_POST['product_code']);
    $type_id       = $_POST['type_id'];
    $unit_id       = $_POST['unit_id'];
    $buying_price  = $_POST['buying_price'];
    $selling_price = $_POST['selling_price'];
    $quantity      = $_POST['quantity'];



    if(empty($product_name) || empty($product_code)){


        $error = "Product name and code required.";


    }else{


        try{


            $stmt = $db->prepare("

                INSERT INTO products

                (
                product_name,
                product_code,
                type_id,
                unit_id,
                buying_price,
                selling_price,
                quantity
                )

                VALUES (?,?,?,?,?,?,?)

            ");


            $stmt->execute([

                $product_name,
                $product_code,
                $type_id,
                $unit_id,
                $buying_price,
                $selling_price,
                $quantity

            ]);



            logActivity(
                $db,
                $_SESSION['user_id'],
                "Added product: ".$product_name
            );


            $msg = "Product added successfully.";


        }catch(PDOException $e){

            $error = "Product code already exists.";

        }

    }

}




// ==========================================
// DELETE PRODUCT
// ==========================================

if(isset($_GET['delete'])){


    $id = $_GET['delete'];


    $stmt = $db->prepare("
        SELECT product_name
        FROM products
        WHERE id=?
    ");

    $stmt->execute([$id]);

    $product = $stmt->fetch(PDO::FETCH_ASSOC);



    $stmt=$db->prepare("
        DELETE FROM products
        WHERE id=?
    ");

    $stmt->execute([$id]);



    logActivity(
        $db,
        $_SESSION['user_id'],
        "Deleted product: ".$product['product_name']
    );


    $msg="Product deleted.";

}



// ==========================================
// FETCH PRODUCTS
// ==========================================


$products = $db->query("

SELECT

p.*,

t.name AS type_name,

u.name AS unit_name


FROM products p


LEFT JOIN product_types t

ON p.type_id=t.id



LEFT JOIN units u

ON p.unit_id=u.id


ORDER BY p.id DESC


")->fetchAll(PDO::FETCH_ASSOC);



$types=$db->query("

SELECT *

FROM product_types

ORDER BY name

")->fetchAll(PDO::FETCH_ASSOC);



$units=$db->query("

SELECT *

FROM units

ORDER BY name

")->fetchAll(PDO::FETCH_ASSOC);



?>



<!DOCTYPE html>

<html>

<head>

<title>Products</title>


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


input,select{

padding:10px;
margin:5px;
width:220px;

}


button{

background:#2563eb;
color:white;
border:0;
padding:10px 20px;
border-radius:5px;

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


.low{

color:red;
font-weight:bold;

}


</style>


</head>


<body>


<div class="container">



<h2>Product Management</h2>


<a href="dashboard.php">
← Dashboard
</a>




<?php if($msg): ?>

<p class="success">
<?= $msg ?>
</p>

<?php endif; ?>



<?php if($error): ?>

<p class="error">
<?= $error ?>
</p>

<?php endif; ?>





<h3>Add Product</h3>


<form method="POST">



<input
name="product_name"
placeholder="Product Name"
required
>



<input
name="product_code"
placeholder="Product Code"
required
>



<select name="type_id">

<option>
Select Type
</option>


<?php foreach($types as $t): ?>

<option value="<?= $t['id'] ?>">

<?= $t['name'] ?>

</option>


<?php endforeach; ?>


</select>





<select name="unit_id">

<option>
Select Unit
</option>


<?php foreach($units as $u): ?>

<option value="<?= $u['id'] ?>">

<?= $u['name'] ?>

</option>


<?php endforeach; ?>


</select>





<input

type="number"

step="0.01"

name="buying_price"

placeholder="Buying Price (<?= getCurrency() ?>)"
required

>




<input

type="number"

step="0.01"

name="selling_price"

placeholder="Selling Price (<?= getCurrency() ?>)"required

>




<input

type="number"

name="quantity"

placeholder="Quantity"

required

>



<button name="add_product">

Save Product

</button>


</form>





<h3>Product List</h3>




<table>


<tr>

<th>ID</th>
<th>Name</th>
<th>Code</th>
<th>Type</th>
<th>Unit</th>
<th>Buying Price</th>
<th>Selling Price</th>
<th>Quantity</th>
<th>Action</th>


</tr>




<?php foreach($products as $p): ?>


<tr>


<td>
<?= $p['id'] ?>
</td>


<td>
<?= $p['product_name'] ?>
</td>


<td>
<?= $p['product_code'] ?>
</td>


<td>
<?= $p['type_name'] ?>
</td>


<td>
<?= $p['unit_name'] ?>
</td>


<td>
<?= getCurrency() . " " . number_format($p['buying_price']) ?>
</td>


<td>
<?= getCurrency() . " " . number_format($p['selling_price']) ?></td>



<td>


<?php if($p['quantity'] <= 10): ?>

<span class="low">

<?= $p['quantity'] ?>

</span>


<?php else: ?>

<?= $p['quantity'] ?>


<?php endif; ?>


</td>




<td>


<a href="?delete=<?= $p['id'] ?>"
onclick="return confirm('Delete product?')">

Delete

</a>


</td>


</tr>



<?php endforeach; ?>



</table>




</div>


</body>

</html>