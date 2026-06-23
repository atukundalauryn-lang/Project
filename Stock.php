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
// STOCK DATA
// ==========================================


$stock = $db->query("

SELECT

p.id,

p.product_name,

p.product_code,

t.name AS type_name,

u.name AS unit_name,

p.quantity,

p.buying_price,

p.selling_price


FROM products p


LEFT JOIN product_types t

ON p.type_id=t.id



LEFT JOIN units u

ON p.unit_id=u.id



ORDER BY p.quantity ASC


")->fetchAll(PDO::FETCH_ASSOC);






// ==========================================
// COUNTS
// ==========================================


$totalItems = $db->query("

SELECT COUNT(*)

FROM products

")->fetchColumn();




$totalQuantity = $db->query("

SELECT COALESCE(SUM(quantity),0)

FROM products

")->fetchColumn();





$lowStock = $db->query("

SELECT COUNT(*)

FROM products

WHERE quantity <= 10

")->fetchColumn();





?>





<!DOCTYPE html>

<html>


<head>


<title>Stock Management</title>


<style>


body{

font-family:Arial;

background:#f4f6f9;

margin:0;

}



.container{

padding:25px;

}



.cards{

display:grid;

grid-template-columns:repeat(3,1fr);

gap:20px;

}



.card{

background:white;

padding:20px;

border-radius:10px;

box-shadow:0 2px 8px #ccc;

}



.card h2{

color:#2563eb;

}




table{

width:100%;

background:white;

border-collapse:collapse;

margin-top:25px;

}




th,td{

padding:12px;

border-bottom:1px solid #ddd;

}



.low{

color:red;

font-weight:bold;

}



.normal{

color:green;

font-weight:bold;

}




a{

text-decoration:none;

}




</style>



</head>





<body>




<div class="container">





<h2>
Stock Management
</h2>



<a href="dashboard.php">
← Dashboard
</a>








<div class="cards">





<div class="card">


<h3>
Products
</h3>


<h2>

<?= $totalItems ?>

</h2>


</div>







<div class="card">


<h3>
Total Quantity
</h3>


<h2>

<?= $totalQuantity ?>

</h2>


</div>







<div class="card">


<h3>
Low Stock Items
</h3>


<h2 class="low">

<?= $lowStock ?>

</h2>


</div>







</div>










<h2>
Available Stock
</h2>





<table>



<tr>


<th>ID</th>

<th>Product</th>

<th>Code</th>

<th>Type</th>

<th>Unit</th>

<th>Quantity</th>

<th>Buying Price</th>

<th>Selling Price</th>

<th>Status</th>



</tr>







<?php foreach($stock as $item): ?>



<tr>



<td>

<?= $item['id'] ?>

</td>





<td>

<?= $item['product_name'] ?>

</td>





<td>

<?= $item['product_code'] ?>

</td>





<td>

<?= $item['type_name'] ?>

</td>





<td>

<?= $item['unit_name'] ?>

</td>






<td>

<?= $item['quantity'] ?>

</td>





<td>

<?= number_format($item['buying_price'],2) ?>

</td>





<td>

<?= number_format($item['selling_price'],2) ?>

</td>






<td>



<?php if($item['quantity'] <= 10): ?>


<span class="low">

LOW STOCK

</span>


<?php else: ?>


<span class="normal">

AVAILABLE

</span>


<?php endif; ?>



</td>






</tr>




<?php endforeach; ?>





</table>







</div>




</body>


</html>