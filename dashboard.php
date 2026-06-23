<?php
require_once 'includes/config.php';


if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
}


$db = getDB();

// ==========================================
// SAVE CURRENCY
// ==========================================

if (isset($_POST['save_currency'])) {
    $_SESSION['currency'] = $_POST['currency'];
}
// Total Products
$stmt = $db->query("
    SELECT COUNT(*) 
    FROM products
");

$totalProducts = $stmt->fetchColumn();



// Total Suppliers
$stmt = $db->query("
    SELECT COUNT(*) 
    FROM suppliers
");

$totalSuppliers = $stmt->fetchColumn();




// Total Godowns
$stmt = $db->query("
    SELECT COUNT(*) 
    FROM godowns
");

$totalGodowns = $stmt->fetchColumn();




// Total Sales
$stmt = $db->query("
    SELECT COALESCE(SUM(total_amount),0)
    FROM sales
    WHERE status='completed'
");

$totalSales = $stmt->fetchColumn();




// Total Purchases
$stmt = $db->query("
    SELECT COALESCE(SUM(total_amount),0)
    FROM purchases
    WHERE status='delivered'
");

$totalPurchases = $stmt->fetchColumn();




// Total Stock
$stmt = $db->query("
    SELECT COALESCE(SUM(quantity),0)
    FROM products
");

$totalStock = $stmt->fetchColumn();




// Low Stock Products

$stmt = $db->query("
SELECT 
product_name,
quantity
FROM products
WHERE quantity <= 10
ORDER BY quantity ASC
LIMIT 5
");

$lowStock = $stmt->fetchAll(PDO::FETCH_ASSOC);



// Recent Sales

$stmt = $db->query("
SELECT 
id,
customer_name,
total_amount,
sale_date
FROM sales
ORDER BY id DESC
LIMIT 5
");

$recentSales = $stmt->fetchAll(PDO::FETCH_ASSOC);



?>



<!DOCTYPE html>

<html>

<head>

<title>Dashboard</title>


<style>

body{
    font-family: Arial, Helvetica, sans-serif;
    background:#f4f6f9;
    margin:0;
}


.header{

background:#1f2937;
color:white;
padding:20px;

display:flex;
justify-content:space-between;

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

margin:0;
color:#2563eb;

}



.menu{

margin-top:25px;

}


.menu a{

display:inline-block;

background:#2563eb;

color:white;

padding:12px 18px;

margin:5px;

text-decoration:none;

border-radius:5px;

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



.low{

color:red;
font-weight:bold;

}



</style>


</head>


<body>



<div class="header">


<h2>
Inventory Management System
</h2>


<div>

Welcome,
<b>
<?php echo $_SESSION['username']; ?>
</b>

|

<a href="logout.php" style="color:white;">
Logout
</a>


</div>


</div>





<div class="container">





<div class="cards">

<div class="card">

<h3>System Currency</h3>

<form method="POST">

<select name="currency" required>

<option value="">Select Currency</option>

<option value="UGX" <?= getCurrency()=='UGX' ? 'selected' : '' ?>>
Uganda Shilling (UGX)
</option>

<option value="KES" <?= getCurrency()=='KES' ? 'selected' : '' ?>>
Kenya Shilling (KES)
</option>

<option value="TZS" <?= getCurrency()=='TZS' ? 'selected' : '' ?>>
Tanzania Shilling (TZS)
</option>

<option value="RWF" <?= getCurrency()=='RWF' ? 'selected' : '' ?>>
Rwanda Franc (RWF)
</option>

<option value="BIF" <?= getCurrency()=='BIF' ? 'selected' : '' ?>>
Burundi Franc (BIF)
</option>

<option value="SSP" <?= getCurrency()=='SSP' ? 'selected' : '' ?>>
South Sudan Pound (SSP)
</option>

<option value="USD" <?= getCurrency()=='USD' ? 'selected' : '' ?>>
US Dollar (USD)
</option>

</select>

<br><br>

<button type="submit" name="save_currency">
Save Currency
</button>

</form>

</div>

<div class="card">


<h3>Total Products</h3>

<h2>
<?php echo $totalProducts; ?>
</h2>

</div>




<div class="card">

<h3>Total Stock</h3>

<h2>
<?php echo $totalStock; ?>
</h2>

</div>





<div class="card">

<h3>Suppliers</h3>

<h2>
<?php echo $totalSuppliers; ?>
</h2>

</div>





<div class="card">

<h3>Godowns</h3>

<h2>
<?php echo $totalGodowns; ?>
</h2>

</div>





<div class="card">

<h3>Total Purchases</h3>

<h2>

<?php echo getCurrency() . ' ' . number_format($totalPurchases); ?>
</h2>

</div>





<div class="card">

<h3>Total Sales</h3>

<h2>

<?php echo getCurrency() . ' ' . number_format($totalSales); ?>
</h2>

</div>



</div>







<div class="menu">


<a href="products.php">
Products
</a>


<a href="suppliers.php">
Suppliers
</a>


<a href="purchases.php">
Purchases
</a>


<a href="sales.php">
Sales
</a>


<a href="reports.php">
Reports
</a>


<a href="users.php">
Users
</a>


</div>






<h2>
Low Stock Alert
</h2>



<table>


<tr>

<th>
Product
</th>

<th>
Quantity
</th>

</tr>



<?php foreach($lowStock as $item): ?>


<tr>

<td>
<?php echo $item['product_name']; ?>
</td>


<td class="low">

<?php echo $item['quantity']; ?>

</td>


</tr>


<?php endforeach; ?>



</table>








<h2>
Recent Sales
</h2>



<table>


<tr>

<th>ID</th>

<th>Customer</th>

<th>Amount</th>

<th>Date</th>


</tr>




<?php foreach($recentSales as $sale): ?>


<tr>


<td>
<?php echo $sale['id']; ?>
</td>



<td>
<?php echo $sale['customer_name']; ?>
</td>




<td>
<?php echo getCurrency() . ' ' . number_format($sale['total_amount']); ?></td>



<td>
<?php echo $sale['sale_date']; ?>
</td>



</tr>


<?php endforeach; ?>



</table>






</div>



</body>


</html>