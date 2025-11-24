<?php
session_start();
require 'db.php';
if(!isset($_SESSION['user_id'])) header("Location: index.php");

// Fetch medicines
$medicines = $pdo->query("SELECT * FROM medicines")->fetchAll(PDO::FETCH_ASSOC);
?>
<!doctype html>
<html lang="en">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title>POS - Pharmacy</title>
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
<script src="https://code.jquery.com/jquery-3.7.0.min.js"></script>
<link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css" rel="stylesheet">
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>

<style>
body { overflow-x: hidden; }
.sidebar { min-height: 100vh; background-color: #0d6efd; color: white; padding-top: 1rem; }
.sidebar a { color: white; text-decoration: none; display: block; padding: 0.75rem 1rem; }
.sidebar a:hover { background-color: rgba(255,255,255,0.1); }
.content { margin-left: 220px; padding: 20px; }
</style>
</head>
<body>
    <?php include 'topbar.php'; ?>

<div class="d-flex">
  <!-- Sidebar -->
 <?php include 'sidebar.php'; ?>
<div class="content-wrapper">
     <div class="content-inner">
<div class="container mt-3">
<h3>Point of Sale</h3>
<div class="row">
<div class="col-md-6">
<h5>Add Items</h5>
<select id="medicineSelect" class="form-select mb-2">
<option value="">Select Medicine</option>
<?php foreach($medicines as $m): ?>
<option value="<?= $m['id'] ?>" data-price="<?= $m['price'] ?>"><?= $m['name'] ?> ($<?= $m['price'] ?>)</option>
<?php endforeach; ?>
</select>
<input type="number" id="quantity" class="form-control mb-2" placeholder="Quantity" value="1">
<button class="btn btn-primary w-100 mb-3" id="addItem">Add to Cart</button>

<h5>Cart</h5>
<table class="table table-bordered" id="cartTable">
<thead><tr><th>Medicine</th><th>Qty</th><th>Price</th><th>Total</th><th>Action</th></tr></thead>
<tbody></tbody>
</table>

<h5>Payment</h5>
<div class="mb-2"><label>Total: $<span id="totalAmount">0</span></label></div>
<div class="mb-2"><label>Paid</label><input type="number" id="paidAmount" class="form-control" value="0"></div>
<div class="mb-2"><label>Due: $<span id="dueAmount">0</span></label></div>
<button class="btn btn-success w-100" id="checkout">Checkout</button>
</div>
</div>
</div>

<script>
let cart = [];
function updateCart(){
    let tbody = '';
    let total = 0;
    cart.forEach((item,i)=>{
        let line = item.price*item.qty;
        total += line;
        tbody += `<tr>
            <td>${item.name}</td>
            <td>${item.qty}</td>
            <td>$${item.price}</td>
            <td>$${line}</td>
            <td><button class="btn btn-sm btn-danger" onclick="removeItem(${i})">X</button></td>
        </tr>`;
    });
    $('#cartTable tbody').html(tbody);
    $('#totalAmount').text(total);
    let paid = parseFloat($('#paidAmount').val());
    $('#dueAmount').text(total-paid);
}

function removeItem(index){
    cart.splice(index,1);
    updateCart();
}

$('#addItem').click(function(){
    let med = $('#medicineSelect option:selected');
    let id = med.val();
    let name = med.text();
    let price = parseFloat(med.data('price'));
    let qty = parseInt($('#quantity').val());
    if(!id || qty<1) return alert('Select medicine and quantity');
    cart.push({id,name,price,qty});
    updateCart();
});

$('#paidAmount').on('input', updateCart);

$('#checkout').click(function(){
    if(cart.length==0) return alert('Cart empty');
    let total = $('#totalAmount').text();
let paid = $('#paidAmount').val();

$.post("pos_checkout.php", {
    cart: JSON.stringify(cart),
    total: total,
    paid: paid
}, function(response){
   if(response === "success") {
    alert("Checkout complete!");
    
    cart = [];
    updateCart();

    // Reset fields
    $('#medicineSelect').val('');
    $('#quantity').val(1);
    $('#paidAmount').val(0);
    $('#dueAmount').text(0);

} else {
    alert("Error: " + response);
}

});

});
</script>
</div> <!-- END .content-wrapper -->
</div>
</body>
</html>
