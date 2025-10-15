<?php
session_start();
$servername = "localhost";
$username = "root";
$password = "";
$dbname = "uber_system";

// Connect to MySQL
$conn = new mysqli($servername, $username, $password, $dbname);
if($conn->connect_error) die("Connection failed: " . $conn->connect_error);

// Handle login
if(isset($_POST['login'])){
    $email = $_POST['email'];
    $password_input = $_POST['password'];
    $stmt = $conn->prepare("SELECT user_id, name, role, password FROM users WHERE email=?");
    $stmt->bind_param("s",$email);
    $stmt->execute();
    $stmt->bind_result($user_id,$name,$role,$hashed_password);
    if($stmt->fetch() && password_verify($password_input,$hashed_password)){
        $_SESSION['user_id']=$user_id;
        $_SESSION['name']=$name;
        $_SESSION['role']=$role;
        header("Location:index.php"); exit;
    } else $error="Invalid login";
}

// Handle logout
if(isset($_GET['logout'])){
    session_destroy();
    header("Location:index.php");
    exit;
}

// Handle AJAX actions
if(isset($_GET['action'])){
    header('Content-Type: application/json');
    $action=$_GET['action'];
    $role=$_SESSION['role'] ?? '';
    $user_id=$_SESSION['user_id'] ?? 0;

    if($action=='vehicles'){
        $res=$conn->query("SELECT v.vehicle_id,v.model,d.name as driver,v.plate_number,v.status
                           FROM vehicles v JOIN users d ON v.driver_id=d.user_id");
        $data=[]; while($row=$res->fetch_assoc()) $data[]=$row; echo json_encode($data); exit;
    }
    if($action=='bookings'){
        if($role=='client'){
            $res=$conn->query("SELECT b.booking_id,v.model as cab,b.pickup,b.dropoff,b.status
                               FROM bookings b JOIN vehicles v ON b.vehicle_id=v.vehicle_id
                               WHERE b.user_id=$user_id");
        } elseif($role=='driver'){
            $res=$conn->query("SELECT b.booking_id,u.name as client,b.pickup,b.dropoff,b.status
                               FROM bookings b JOIN users u ON b.user_id=u.user_id
                               JOIN vehicles v ON b.vehicle_id=v.vehicle_id
                               WHERE v.driver_id=$user_id");
        } else {
            $res=$conn->query("SELECT b.booking_id,u.name as client,v.model as cab,b.pickup,b.dropoff,b.status
                               FROM bookings b JOIN users u ON b.user_id=u.user_id
                               JOIN vehicles v ON b.vehicle_id=v.vehicle_id");
        }
        $data=[]; while($row=$res->fetch_assoc()) $data[]=$row; echo json_encode($data); exit;
    }
    if($action=='payments'){
        if($role=='client'){
            $res=$conn->query("SELECT p.payment_id,p.booking_id,p.amount,p.method,p.status
                               FROM payments p JOIN bookings b ON p.booking_id=b.booking_id
                               WHERE b.user_id=$user_id");
        } else {
            $res=$conn->query("SELECT * FROM payments");
        }
        $data=[]; while($row=$res->fetch_assoc()) $data[]=$row; echo json_encode($data); exit;
    }
}

// Handle booking submission
if(isset($_POST['book_cab'])){
    $user_id=$_SESSION['user_id'];
    $vehicle_id=$_POST['vehicle_id'];
    $pickup=$_POST['pickup'];
    $dropoff=$_POST['dropoff'];
    $stmt=$conn->prepare("INSERT INTO bookings (user_id,vehicle_id,pickup,dropoff) VALUES (?,?,?,?)");
    $stmt->bind_param("iiss",$user_id,$vehicle_id,$pickup,$dropoff);
    if($stmt->execute()) $msg="Booking successful!";
    else $msg="Failed to book.";
}

// Handle status update
if(isset($_POST['update_status'])){
    $booking_id=$_POST['booking_id'];
    $status=$_POST['status'];
    $stmt=$conn->prepare("UPDATE bookings SET status=? WHERE booking_id=?");
    $stmt->bind_param("si",$status,$booking_id);
    $stmt->execute();
    $msg="Status updated!";
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>Uber Booking System</title>
<meta name="viewport" content="width=device-width, initial-scale=1">
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
<script src="https://cdn.jsdelivr.net/npm/jquery@3.7.1/dist/jquery.min.js"></script>
<style>
body{background:#f4f6f9}.nav-link{cursor:pointer}.card{border-radius:10px}.dashboard-section{display:none}.active-section{display:block}
</style>
</head>
<body>

<?php if(!isset($_SESSION['user_id'])): ?>
<!-- Login Form -->
<div class="container mt-5">
<div class="row justify-content-center">
<div class="col-md-4 bg-white p-4 rounded shadow">
<h3 class="text-center mb-3">Login</h3>
<?php if(isset($error)) echo "<div class='alert alert-danger'>$error</div>"; ?>
<form method="post">
<div class="mb-3"><label>Email</label><input type="email" name="email" class="form-control" required></div>
<div class="mb-3"><label>Password</label><input type="password" name="password" class="form-control" required></div>
<button class="btn btn-primary w-100" name="login">Login</button>
</form>
</div></div></div>
<?php else: ?>
<!-- Dashboard -->
<nav class="navbar navbar-expand navbar-light bg-white shadow-sm p-2">
<span class="navbar-brand fw-bold">🚖 Uber System</span>
<ul class="navbar-nav ms-auto">
<li class="nav-item"><a class="nav-link" href="?logout=1">Logout</a></li>
</ul>
</nav>

<div class="container mt-3">
<ul class="nav nav-pills mb-3">
<li class="nav-item"><a class="nav-link active" onclick="showSection('dashboard')">Dashboard</a></li>
<li class="nav-item"><a class="nav-link" onclick="showSection('vehicles')">Vehicles</a></li>
<li class="nav-item"><a class="nav-link" onclick="showSection('bookings')">Bookings</a></li>
<li class="nav-item"><a class="nav-link" onclick="showSection('payments')">Payments</a></li>
</ul>

<div id="dashboard" class="dashboard-section active-section">
<h3>Welcome, <?=$_SESSION['name']?> (<?=$_SESSION['role']?>)</h3>
<p>Use the tabs above to manage the system.</p>
</div>

<div id="vehicles" class="dashboard-section">
<h4>Vehicles</h4>
<table class="table table-bordered">
<thead><tr><th>ID</th><th>Model</th><th>Driver</th><th>Plate</th><th>Status</th><th>Action</th></tr></thead>
<tbody id="vehicles-tbody"></tbody>
</table>
</div>

<div id="bookings" class="dashboard-section">
<h4>Bookings</h4>
<table class="table table-bordered">
<thead><tr><th>ID</th>
<?php if($_SESSION['role']=='driver') echo "<th>Client</th>"; ?>
<?php if($_SESSION['role']=='client') echo "<th>Cab</th>"; ?>
<th>Pickup</th><th>Dropoff</th><th>Status</th><th>Action</th></tr></thead>
<tbody id="bookings-tbody"></tbody>
</table>
</div>

<div id="payments" class="dashboard-section">
<h4>Payments</h4>
<table class="table table-bordered">
<thead><tr><th>ID</th><th>Booking</th><th>Amount</th><th>Method</th><th>Status</th></tr></thead>
<tbody id="payments-tbody"></tbody>
</table>
</div>
</div>

<script>
function showSection(id){
$('.dashboard-section').removeClass('active-section');
$('#'+id).addClass('active-section');
$('.nav-link').removeClass('active');
$('a[onclick="showSection(\''+id+'\')"]').addClass('active');
}

function loadVehicles(){
$.get('index.php?action=vehicles',function(data){
let html='';
data.forEach(v=>{
html+=`<tr>
<td>${v.vehicle_id}</td>
<td>${v.model}</td>
<td>${v.driver}</td>
<td>${v.plate_number}</td>
<td>${v.status}</td>`;
<?php if($_SESSION['role']=='client'): ?>
html+=`<td><button class="btn btn-sm btn-success" onclick="bookCab(${v.vehicle_id})">Book Now</button></td>`;
<?php endif; ?>
html+=`</tr>`;
});
$('#vehicles-tbody').html(html);
});

}

function loadBookings(){
$.get('index.php?action=bookings',function(data){
let html='';
data.forEach(b=>{
html+='<tr>';
html+=`<td>${b.booking_id}</td>`;
<?php if($_SESSION['role']=='driver'): ?>
html+=`<td>${b.client}</td>`;
<?php endif; ?>
<?php if($_SESSION['role']=='client'): ?>
html+=`<td>${b.cab}</td>`;
<?php endif; ?>
html+=`<td>${b.pickup}</td><td>${b.dropoff}</td><td>${b.status}</td><td>`;
<?php if($_SESSION['role']=='driver'): ?>
if(b.status=='Confirmed') html+=`<button class="btn btn-sm btn-warning" onclick="updateStatus(${b.booking_id},'Picked Up')">Picked Up</button>`;
else if(b.status=='Picked Up') html+=`<button class="btn btn-sm btn-success" onclick="updateStatus(${b.booking_id},'Completed')">Dropped Off</button>`;
<?php endif; ?>
html+='</td></tr>';
});
$('#bookings-tbody').html(html);
});

}

function loadPayments(){
$.get('index.php?action=payments',function(data){
let html='';
data.forEach(p=>{
html+=`<tr>
<td>${p.payment_id}</td>
<td>${p.booking_id}</td>
<td>${p.amount}</td>
<td>${p.method}</td>
<td>${p.status}</td>
</tr>`;
});
$('#payments-tbody').html(html);
});

}

function bookCab(vehicle_id){
let pickup=prompt("Enter Pickup Location:");
if(!pickup) return;
let dropoff=prompt("Enter Dropoff Location:");
if(!dropoff) return;
$.post('index.php',{book_cab:1,vehicle_id,pickup,dropoff},function(res){
alert("Booking sent! Refreshing...");
loadBookings();
});
}

function updateStatus(booking_id,status){
$.post('index.php',{update_status:1,booking_id,status},function(res){
alert("Status updated!");
loadBookings();
});
}

$(document).ready(function(){
loadVehicles();
loadBookings();
loadPayments();
});
</script>

<?php endif; ?>
</body>
</html>
