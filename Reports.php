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
// SUMMARY DATA
// ==========================================


// Products

$totalProducts = $db->query("

SELECT COUNT(*)
FROM products

")->fetchColumn();




// Stock

$totalStock = $db->query("

SELECT COALESCE(SUM(quantity),0)
FROM products

")->fetchColumn();




// Purchases

$totalPurchases = $db->query("

SELECT COALESCE(SUM(total_amount),0)
FROM purchases
WHERE status='delivered'

")->fetchColumn();




// Sales

$totalSales = $db->query("

SELECT COALESCE(SUM(total_amount),0)
FROM sales
WHERE status='completed'

")->fetchColumn();





// ==========================================
// STOCK REPORT
// ==========================================


$stockReport = $db->query("

SELECT *

FROM current_stock

ORDER BY product_name

")->fetchAll(PDO::FETCH_ASSOC);





// ==========================================
// PURCHASE REPORT
// ==========================================


$purchaseReport = $db->query("


SELECT

p.id,

p.invoice_no,

s.name supplier,

g.name godown,

p.total_amount,

p.status,

p.purchase_date


FROM purchases p


LEFT JOIN suppliers s

ON p.supplier_id=s.id



LEFT JOIN godowns g

ON p.godown_id=g.id



ORDER BY p.id DESC



")->fetchAll(PDO::FETCH_ASSOC);







// ==========================================
// SALES REPORT
// ==========================================


$salesReport = $db->query("



SELECT

s.id,

s.customer_name,

g.name godown,

s.total_amount,

s.status,

s.sale_date


FROM sales s


LEFT JOIN godowns g

ON s.godown_id=g.id


ORDER BY s.id DESC



")->fetchAll(PDO::FETCH_ASSOC);





?>





<!DOCTYPE html>

<html>


<head>


<title>Reports</title>


<style>


body{

font-family:Arial;

background:#f4f6f9;

}



.container{

padding:25px;

}



.cards{

display:grid;

grid-template-columns:repeat(4,1fr);

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

margin-top:20px;

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
Inventory Reports
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
<?= $totalProducts ?>
</h2>

</div>





<div class="card">

<h3>
Stock Quantity
</h3>

<h2>
<?= $totalStock ?>
</h2>

</div>





<div class="card">

<h3>
Purchases
</h3>

<h2>
<?= number_format($totalPurchases,2) ?>
</h2>

</div>





<div class="card">

<h3>
Sales
</h3>

<h2>
<?= number_format($totalSales,2) ?>
</h2>

</div>





</div>









<h2>
Stock Report
</h2>




<table>


<tr>

<th>
Product
</th>

<th>
Code
</th>

<th>
Stock
</th>

<th>
Buying Price
</th>

<th>
Selling Price
</th>


</tr>




<?php foreach($stockReport as $r): ?>

<tr>


<td>
<?= $r['product_name'] ?>
</td>



<td>
<?= $r['product_code'] ?>
</td>



<td>

<?= $r['available_stock'] ?>

</td>




<td>

<?= number_format($r['buying_price'],2) ?>

</td>




<td>

<?= number_format($r['selling_price'],2) ?>

</td>



</tr>


<?php endforeach; ?>



</table>









<h2>
Purchase Report
</h2>




<table>


<tr>

<th>ID</th>

<th>Invoice</th>

<th>Supplier</th>

<th>Godown</th>

<th>Total</th>

<th>Status</th>

<th>Date</th>


</tr>





<?php foreach($purchaseReport as $p): ?>


<tr>


<td>
<?= $p['id'] ?>
</td>



<td>
<?= $p['invoice_no'] ?>
</td>



<td>
<?= $p['supplier'] ?>
</td>



<td>
<?= $p['godown'] ?>
</td>




<td>
<?= number_format($p['total_amount'],2) ?>
</td>




<td>
<?= $p['status'] ?>
</td>




<td>
<?= $p['purchase_date'] ?>
</td>



</tr>



<?php endforeach; ?>



</table>









<h2>
Sales Report
</h2>





<table>


<tr>

<th>ID</th>

<th>Customer</th>

<th>Godown</th>

<th>Total</th>

<th>Status</th>

<th>Date</th>


</tr>






<?php foreach($salesReport as $s): ?>


<tr>


<td>
<?= $s['id'] ?>
</td>



<td>
<?= $s['customer_name'] ?>
</td>




<td>
<?= $s['godown'] ?>
</td>




<td>
<?= number_format($s['total_amount'],2) ?>
</td>




<td>
<?= $s['status'] ?>
</td>



<td>
<?= $s['sale_date'] ?>
</td>



</tr>



<?php endforeach; ?>




</table>





</div>





</body>


</html>