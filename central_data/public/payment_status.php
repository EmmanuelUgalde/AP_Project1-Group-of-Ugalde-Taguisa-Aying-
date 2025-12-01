<?php
session_start();
include __DIR__ . "/../config/Database.php";

// Check if user is logged in
if (!isset($_SESSION['role'])) {
    echo "<div style='text-align:center; margin-top:200px; font-family: Port Lligat Slab, serif;'>
            <h1 style='color:red;'>Access Denied</h1>
            <p>You must be logged in to access this page.</p>
          </div>";
    exit;
}

$currentRole = strtolower($_SESSION['role']); 

if (!in_array($currentRole, ['superadmin', 'staff'])) {
    echo "<div style='display:flex; justify-content:center; align-items:center; height:calc(100vh - 60px); flex-direction:column; text-align:center; font-family:Port Lligat Slab, serif; color:red; font-size:50px;'>
        <h1>Access Denied</h1>
        <p style='color:black;'>Only SuperAdmin or Staff can access this page.</p>
    </div>";
    exit;
}

$db = new Database();
$conn = $db->getConn();
$message = "";

if (isset($_POST['add_status'])) {
    $pymt_stat_name = $_POST['pymt_stat_name'];
    $stmt = $conn->prepare("INSERT INTO PAYMENT_STATUS (PYMT_STAT_NAME) VALUES (?)");
    $stmt->bind_param("s", $pymt_stat_name);
    if ($stmt->execute()) {
        $message = "Payment status added successfully!";
    } else {
        $message = "Error adding payment status: " . $stmt->error;
    }
    $stmt->close();
}

if (isset($_POST['update_status'])) {
    $pymt_stat_id = (int)$_POST['pymt_stat_id'];
    $pymt_stat_name = $_POST['pymt_stat_name'];
    $stmt = $conn->prepare("UPDATE PAYMENT_STATUS SET PYMT_STAT_NAME = ? WHERE PYMT_STAT_ID = ?");
    $stmt->bind_param("si", $pymt_stat_name, $pymt_stat_id);
    if ($stmt->execute()) {
        $message = "Payment status updated successfully!";
    } else {
        $message = "Error updating payment status: " . $stmt->error;
    }
    $stmt->close();
}

if ($currentRole === 'superadmin' && isset($_POST['delete_status'])) {
    $deleteId = (int)$_POST['delete_status'];
    $check = $conn->prepare("SELECT COUNT(*) FROM PAYMENT WHERE PYMT_STAT_ID = ?");
    $check->bind_param("i", $deleteId);
    $check->execute();
    $check->bind_result($count);
    $check->fetch();
    $check->close();

    if ($count > 0) {
        $message = "Error: Cannot delete this status because it is used in $count payment(s).";
    } else {
        $stmt = $conn->prepare("DELETE FROM PAYMENT_STATUS WHERE PYMT_STAT_ID = ?");
        $stmt->bind_param("i", $deleteId);
        if ($stmt->execute()) {
            $message = "Payment status deleted successfully!";
        } else {
            $message = "Error deleting payment status: " . $stmt->error;
        }
        $stmt->close();
    }
}

$search = $_GET['search'] ?? '';
if ($search) {
    $stmt = $conn->prepare("SELECT * FROM PAYMENT_STATUS WHERE PYMT_STAT_NAME LIKE ? ORDER BY PYMT_STAT_ID ASC");
    $like = "%$search%";
    $stmt->bind_param("s", $like);
} else {
    $stmt = $conn->prepare("SELECT * FROM PAYMENT_STATUS ORDER BY PYMT_STAT_ID ASC");
}
$stmt->execute();
$result = $stmt->get_result();
$statusList = $result->fetch_all(MYSQLI_ASSOC);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Payment Status - Urban Medical Hospital</title>
    <style>
        html, body {
            height: 100%;
            margin: 0;
            font-family: 'Port Lligat Slab', serif;
            background-color: #FFF9F9;
            display: flex;
            flex-direction: column;
        }

        .container-page {
            flex: 1;
            padding: 70px;
        }

        .container-page h1 {
            font-size: 80px;
            color: black;
            margin-top: 120px;
            text-align: left;
        }

        .action-bar {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin: 20px 0;
        }

        .container-page .search-bar {
            background-color: #659BDF;
            height: 135px;
            margin-left: -5px;
            padding: 40px 40px;
            border-radius: 30px;
        }

        .container-page .search-bar input {
            padding: 16px 12px;
            width: 250px;
            height: 50px;
            border: 1px solid #ccc;
            border-radius: 5px;
            margin-left: -5px;
            margin-top: 5px;
        }

        .container-page .search-bar form {
            display: flex;
            gap: 10px;
        }

        .container-page .search-bar button {
            width: 100px;
        }

        .container-page .search-bar button:hover {
            background-color: #66b3ff;
            transition: background-color 0.3s ease;
        }

        .btn {
            padding: 8px 14px;
            border: none;
            border-radius: 5px;
            cursor: pointer;
            font-weight: bold;
        }

        .btn-add { 
            font-size: 19px; 
            background-color: #28a745; 
            color: white; 
            width: 150px; 
            height: 60px; 
            margin-right: 15px;
        }
        
        .btn-update { 
            font-size: 20px; 
            background-color: #007bff; 
            color: white; 
            width: 150px; 
            height: 70px;
        }
        
        .btn-delete { 
            font-size: 20px; 
            background-color: #dc3545; 
            color: white; 
            width: 150px; 
            height: 50px;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 25px;
            background: white;
        }

        th, td {
            padding: 12px;
            text-align: center;
            border-bottom: 1px solid #ddd;
            font-size: 20px;
        }

        th {
            background-color: #f1f1f1;
        }

        tr:hover {
            background-color: #f9f9f9;
        }

        .message {
            margin: 10px 0;
            padding: 15px;
            border-radius: 5px;
            font-weight: bold;
            background-color: #d4edda;
            color: #155724;
            border: 1px solid #c3e6cb;
        }

        .message.error {
            background-color: #f8d7da;
            color: #721c24;
            border-color: #f5c6cb;
        }

        .add-form-container {
            position: fixed;
            top: 50%;
            left: 50%;
            transform: translate(-50%, 30%) scale(0.9);
            background: white;
            padding: 25px;
            border-radius: 12px;
            box-shadow: 0 4px 15px rgba(0, 0, 0, 0.3);
            width: 400px;
            max-width: 90%;
            z-index: 2000;
            display: none;
            opacity: 0;
            transition: all 0.4s ease;
        }

        .add-form-container.show {
            display: block;
            opacity: 1;
            transform: translate(-50%, -50%) scale(1);
        }

        .add-form-container.hiding {
            opacity: 0;
            transform: translate(-50%, 30%) scale(0.9);
        }

        .overlay {
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: rgba(0, 0, 0, 0.5);
            z-index: 1000;
            opacity: 0;
            pointer-events: none;
            transition: opacity 0.3s ease;
        }

        .overlay.show {
            opacity: 1;
            pointer-events: all;
        }

        .add-form-container h2 {
            text-align: center;
            margin-bottom: 20px;
        }

        .add-form-container label {
            display: block;
            font-weight: bold;
            margin-top: 10px;
        }

        .add-form-container input,
        .add-form-container select {
            width: 100%;
            padding: 12px 10px;
            margin-top: 5px;
            border: 1px solid #ccc;
            border-radius: 5px;
            font-size: 16px;
            font-family: 'Port Lligat Slab', serif;
            background-color: white;
            cursor: pointer;
            height: 45px;
            box-sizing: border-box;
        }

        .add-form-container select:hover {
            border-color: #007bff;
        }

        .add-form-container select:focus,
        .add-form-container input:focus {
            outline: none;
            border-color: #007bff;
            box-shadow: 0 0 5px rgba(0, 123, 255, 0.3);
        }

        .add-form-container select option {
            padding: 10px;
            font-size: 15px;
        }

        .form-actions {
            display: flex;
            justify-content: space-between;
            margin-top: 20px;
            gap: 15px;
        }

        footer {
            width: 100%;
            background-color: #0041c7;
            color: white;
            padding: 30px 50px;
            display: flex;
            justify-content: center;
            align-items: center;
        }
        
        footer h2 {
            font-family: var(--brand-serif);
            font-weight: 400;
            letter-spacing: 0.5px;
        }
    </style>
</head>
<body>
<?php include "../includes/header.php"; ?>

<div class="container-page">
<h1>Payment Status</h1>

<?php if (!empty($message)): ?>
<div class="message <?= strpos($message,'Error')!==false?'error':'' ?>"><?= htmlspecialchars($message) ?></div>
<?php endif; ?>

<div class="action-bar">
<div class="search-bar">
<form method="GET">
<input type="text" name="search" placeholder="Search payment status..." value="<?= htmlspecialchars($search) ?>">
<button class="btn btn-update" type="submit" style="height:60px;">Search</button>
</form>
</div>
<button class="btn btn-add" onclick="showAddForm()" style="height:70px;">Add Payment Status</button>
</div>

<table>
<tr><th>ID</th><th>Status Name</th><th>Actions</th></tr>
<?php if (!empty($statusList)): ?>
<?php foreach ($statusList as $s): ?>
<tr>
<td><?= $s['PYMT_STAT_ID'] ?></td>
<td><?= htmlspecialchars($s['PYMT_STAT_NAME']) ?></td>
<td>
<button type="button" class="btn btn-update" style="height:40px; width:100px; margin-right:10px;"
onclick="showUpdateForm(<?= $s['PYMT_STAT_ID'] ?>, '<?= htmlspecialchars($s['PYMT_STAT_NAME'],ENT_QUOTES) ?>')">Edit</button>
<?php if ($currentRole==='superadmin'): ?>
<form method="POST" style="display:inline;" onsubmit="return confirm('Are you sure you want to delete this status?');">
<input type="hidden" name="delete_status" value="<?= $s['PYMT_STAT_ID'] ?>">
<button type="submit" class="btn btn-delete" style="width:100px;height:40px;">Delete</button>
</form>
<?php else: ?>
<button type="button" class="btn btn-delete" style="width:100px;height:40px;opacity:0.5;cursor:not-allowed;" onclick="alert('Only SuperAdmin can delete payment statuses!')">Delete</button>
<?php endif; ?>
</td>
</tr>
<?php endforeach; ?>
<?php else: ?>
<tr><td colspan="3">No payment status found.</td></tr>
<?php endif; ?>
</table>

<div id="addForm" class="add-form-container">
<form method="POST">
<h2>Add Payment Status</h2>
<label>Status Name:</label>
<select name="pymt_stat_name" required>
<option value="">-- Select Status --</option>
<option value="Paid">Paid</option>
<option value="Pending">Pending</option>
<option value="Refunded">Refunded</option>
</select>
<div class="form-actions">
<button type="submit" name="add_status" class="btn btn-add">Save</button>
<button type="button" class="btn btn-delete" onclick="hideAddForm()" style="height: 60px;">Cancel</button>
</div>
</form>
</div>

<div id="updateForm" class="add-form-container">
<form method="POST">
<h2>Update Payment Status</h2>
<input type="hidden" name="pymt_stat_id" id="update_id">
<label>Status Name:</label>
<select name="pymt_stat_name" id="update_name" required>
<option value="">-- Select Status --</option>
<option value="Paid">Paid</option>
<option value="Pending">Pending</option>
<option value="Refunded">Refunded</option>
</select>
<div class="form-actions">
<button type="submit" name="update_status" class="btn btn-add">Update</button>
<button type="button" class="btn btn-delete" onclick="hideUpdateForm()">Cancel</button>
</div>
</form>
</div>

<div id="overlay" class="overlay"></div>
</div>

<footer>
<h2>&copy; 2025 Urban Medical Hospital. All Rights Reserved.</h2>
</footer>

<script>
function showAddForm(){document.getElementById('overlay').classList.add('show');const f=document.getElementById('addForm');f.style.display='block';setTimeout(()=>f.classList.add('show'),10);}
function hideAddForm(){const f=document.getElementById('addForm');f.classList.remove('show');document.getElementById('overlay').classList.remove('show');setTimeout(()=>f.style.display='none',400);}
function showUpdateForm(id,name){document.getElementById('overlay').classList.add('show');const f=document.getElementById('updateForm');f.style.display='block';setTimeout(()=>f.classList.add('show'),10);document.getElementById('update_id').value=id;document.getElementById('update_name').value=name;}
function hideUpdateForm(){const f=document.getElementById('updateForm');f.classList.remove('show');document.getElementById('overlay').classList.remove('show');setTimeout(()=>f.style.display='none',400);}
document.getElementById('overlay').addEventListener('click',function(){hideAddForm();hideUpdateForm();});
</script>
</body>
</html>