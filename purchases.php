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

    $stmt = $db->prepare("

    INSERT INTO staff_activity
    (
        user_id,
        action
    )

    VALUES(?,?)

    ");


    $stmt->execute([

        $user,
        $action

    ]);

}





// ==========================================
// SAVE PURCHASE
// ==========================================


if(isset($_POST['save_purchase'])){


    $supplier_id = $_POST['supplier_id'];
    $godown_id   = $_POST['godown_id'];
    $invoice_no  = $_POST['invoice_no'];
    $date        = $_POST['purchase_date'];


    $product_id  = $_POST['product_id'];
    $quantity    = $_POST['quantity'];
    $price       = $_POST['price'];




    try{


        $db->beginTransaction();



        $total = $quantity * $price;





        // INSERT PURCHASE


        $stmt = $db->prepare("

        INSERT INTO purchases

        (
        supplier_id,
        godown_id,
        invoice_no,
        purchase_date,
        total_amount,
        status,
        created_by
        )

        VALUES(?,?,?,?,?,?,?)

        ");



        $stmt->execute([


            $supplier_id,
            $godown_id,
            $invoice_no,
            $date,
            $total,
            'delivered',
            $_SESSION['user_id']


        ]);





        $purchase_id = $db->lastInsertId();








        // INSERT PURCHASE ITEM


        $stmt = $db->prepare("

        INSERT INTO purchase_items

        (
        purchase_id,
        product_id,
        quantity,
        price
        )

        VALUES(?,?,?,?)

        ");



        $stmt->execute([


            $purchase_id,
            $product_id,
            $quantity,
            $price


        ]);









        // UPDATE STOCK


        $stmt = $db->prepare("

        UPDATE products

        SET quantity = quantity + ?

        WHERE id=?

        ");



        $stmt->execute([


            $quantity,
            $product_id


        ]);








        logActivity(

            $db,

            $_SESSION['user_id'],

            "Created purchase ID ".$purchase_id

        );






        $db->commit();



        $msg="Purchase saved successfully.";





    }catch(Exception $e){



        $db->rollBack();


        $error="Purchase failed: ".$e->getMessage();



    }



}







// ==========================================
// DELETE PURCHASE
// ==========================================


if(isset($_GET['delete'])){


    $id=$_GET['delete'];



    $stmt=$db->prepare("

    DELETE FROM purchases

    WHERE id=?

    ");



    $stmt->execute([$id]);





    logActivity(

        $db,

        $_SESSION['user_id'],

        "Deleted purchase ID ".$id

    );



    $msg="Purchase deleted.";

}








// ==========================================
// DATA
// ==========================================


$suppliers=$db->query("

SELECT *

FROM suppliers

ORDER BY name

")->fetchAll(PDO::FETCH_ASSOC);





$godowns=$db->query("

SELECT *

FROM godowns

ORDER BY name

")->fetchAll(PDO::FETCH_ASSOC);






$products=$db->query("

SELECT *

FROM products

ORDER BY product_name

")->fetchAll(PDO::FETCH_ASSOC);






$purchases=$db->query("


SELECT


p.*,

s.name supplier,

g.name godown



FROM purchases p



LEFT JOIN suppliers s

ON p.supplier_id=s.id



LEFT JOIN godowns g

ON p.godown_id=g.id




ORDER BY p.id DESC



")->fetchAll(PDO::FETCH_ASSOC);




?>





<!DOCTYPE html>

<html>


<head>


<title>Purchases</title>


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

width:100%;

box-shadow:0 2px 8px #ccc;

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



</style>


</head>





<body>





<h2>
Purchase Management
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
Add Purchase
</h3>






<form method="POST">






<select name="supplier_id" required>


<option>

Select Supplier

</option>



<?php foreach($suppliers as $s): ?>


<option value="<?=$s['id']?>">

<?=$s['name']?>


</option>


<?php endforeach; ?>



</select>







<select name="godown_id" required>


<option>

Select Godown

</option>



<?php foreach($godowns as $g): ?>


<option value="<?=$g['id']?>">

<?=$g['name']?>


</option>


<?php endforeach; ?>



</select>







<input

name="invoice_no"

placeholder="Invoice No"

required

>






<input

type="date"

name="purchase_date"

required

>







<select name="product_id">



<?php foreach($products as $p): ?>

<option value="<?=$p['id']?>">

<?=$p['product_name']?>


</option>


<?php endforeach; ?>



</select>






<input

type="number"

name="quantity"

placeholder="Quantity"

required

>






<input

type="number"

step="0.01"

name="price"

placeholder="Price"

required

>






<button name="save_purchase">

Save Purchase

</button>





</form>










<h3>
Purchase History
</h3>







<table>


<tr>


<th>ID</th>

<th>Supplier</th>

<th>Godown</th>

<th>Invoice</th>

<th>Total</th>

<th>Status</th>

<th>Date</th>



</tr>








<?php foreach($purchases as $p): ?>



<tr>


<td>
<?=$p['id']?>
</td>



<td>
<?=$p['supplier']?>
</td>




<td>
<?=$p['godown']?>
</td>



<td>
<?=$p['invoice_no']?>
</td>



<td>
<?=number_format($p['total_amount'],2)?>
</td>




<td>
<?=$p['status']?>
</td>




<td>
<?=$p['purchase_date']?>
</td>




</tr>




<?php endforeach; ?>







</table>







</body>


</html>