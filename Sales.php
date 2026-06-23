<?php

require_once 'includes/config.php';


if(!isset($_SESSION['user_id'])){

    header("Location: login.php");
    exit;

}


$db=getDB();

$msg="";
$error="";



function logActivity($db,$user,$action){

$stmt=$db->prepare("
INSERT INTO staff_activity(user_id,action)
VALUES(?,?)
");

$stmt->execute([$user,$action]);

}





if(isset($_POST['save_sale'])){


$customer=$_POST['customer_name'];
$godown=$_POST['godown_id'];
$date=$_POST['sale_date'];

$product=$_POST['product_id'];
$qty=$_POST['quantity'];
$price=$_POST['price'];



try{


$db->beginTransaction();



$stmt=$db->prepare("
SELECT quantity 
FROM products
WHERE id=?
FOR UPDATE
");

$stmt->execute([$product]);

$p=$stmt->fetch();



if($p['quantity']<$qty){

throw new Exception("Insufficient stock");

}



$total=$qty*$price;



$stmt=$db->prepare("
INSERT INTO sales
(customer_name,godown_id,sale_date,total_amount,status,created_by)
VALUES(?,?,?,?,?,?)
");


$stmt->execute([

$customer,
$godown,
$date,
$total,
'completed',
$_SESSION['user_id']

]);



$sale=$db->lastInsertId();




$stmt=$db->prepare("
INSERT INTO sale_items
(sale_id,product_id,quantity,price)
VALUES(?,?,?,?)
");


$stmt->execute([
$sale,
$product,
$qty,
$price
]);




$stmt=$db->prepare("
UPDATE products
SET quantity=quantity-?
WHERE id=?
");


$stmt->execute([
$qty,
$product
]);



$db->commit();



logActivity(
$db,
$_SESSION['user_id'],
"Created sale: ".$sale
);



$msg="Sale completed";



}catch(Exception $e){

$db->rollBack();

$error=$e->getMessage();

}



}






$godowns=$db->query("
SELECT * FROM godowns
")->fetchAll(PDO::FETCH_ASSOC);



$products=$db->query("
SELECT * FROM products
ORDER BY product_name
")->fetchAll(PDO::FETCH_ASSOC);





$sales=$db->query("
SELECT 
s.*,
g.name godown

FROM sales s

LEFT JOIN godowns g
ON s.godown_id=g.id

ORDER BY s.id DESC

")->fetchAll(PDO::FETCH_ASSOC);



?>



<!DOCTYPE html>

<html>

<head>

<title>Sales</title>

<style>

body{
font-family:Arial;
background:#f4f6f9;
padding:20px;
}

form,table{
background:white;
padding:20px;
width:100%;
border-radius:10px;
}


input,select{
padding:10px;
margin:5px;
}


button{
background:green;
color:white;
padding:10px;
border:0;
}


table{
border-collapse:collapse;
margin-top:20px;
}


td,th{
padding:10px;
border-bottom:1px solid #ddd;
}

</style>

</head>


<body>


<h2>Sales Management</h2>


<a href="dashboard.php">Dashboard</a>



<p style="color:green">
<?=$msg?>
</p>


<p style="color:red">
<?=$error?>
</p>




<form method="POST">


<input name="customer_name" placeholder="Customer" required>


<select name="godown_id">

<?php foreach($godowns as $g): ?>

<option value="<?=$g['id']?>">

<?=$g['name']?>

</option>

<?php endforeach; ?>

</select>




<input type="date" name="sale_date" required>





<select name="product_id">

<?php foreach($products as $p): ?>

<option value="<?=$p['id']?>">

<?=$p['product_name']?> 
(stock <?=$p['quantity']?>)

</option>

<?php endforeach; ?>

</select>



<input type="number" name="quantity" placeholder="Quantity">


<input type="number" step="0.01" name="price" placeholder="Price">



<button name="save_sale">
Complete Sale
</button>


</form>





<table>


<tr>

<th>ID</th>
<th>Customer</th>
<th>Total</th>
<th>Date</th>

</tr>


<?php foreach($sales as $s): ?>


<tr>

<td><?=$s['id']?></td>

<td><?=$s['customer_name']?></td>

<td><?=$s['total_amount']?></td>

<td><?=$s['sale_date']?></td>


</tr>


<?php endforeach; ?>


</table>


</body>
</html>