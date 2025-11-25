<?php
session_start();
include __DIR__ . "/../config/Database.php";

if (!isset($_SESSION['role'])) {
    echo "<div style='text-align:center; margin-top:200px; font-family: Port Lligat Slab, serif;'>
            <h1 style='color:red;'>Access Denied</h1>
            <p>You must be logged in to access this page.</p>
          </div>";
    exit;
}

$currentRole = strtolower($_SESSION['role']);

if (!in_array($currentRole, ['superadmin', 'staff'])) {
    echo "
    <div style='
        display: flex;
        justify-content: center;
        align-items: center;
        height: calc(100vh - 60px);
        flex-direction: column;
        text-align: center;
        font-family: \"Port Lligat Slab\", serif;
        color: red;
        font-size: 50px;
    '>
        <h1>Access Denied</h1>
        <p style='color: black;'>Only SuperAdmin or Staff can access this page.</p>
    </div>
    ";
    exit;
}

$db = new Database();
$conn = $db->getConn();

$appts = $conn->query("SELECT APPT_ID FROM APPOINTMENT")->fetch_all(MYSQLI_ASSOC);

$message = "";

if (isset($_POST['add_payment'])) {
    $amount = $_POST['paymt_amount_paid'];
    $date = $_POST['paymt_date'];
    $methodId = $_POST['pymt_meth_id'];
    $statusId = $_POST['pymt_stat_id'];
    $apptId = $_POST['appt_id'];

    $stmtCheck = $conn->prepare("SELECT 1 FROM APPOINTMENT WHERE APPT_ID=?");
    $stmtCheck->bind_param("i", $apptId);
    $stmtCheck->execute();
    if ($stmtCheck->get_result()->num_rows === 0) {
        $message = "Selected appointment does not exist!";
    } else {
        $stmt = $conn->prepare("INSERT INTO PAYMENT (PAYMT_AMOUNT_PAID, PAYMT_DATE, PYMT_METH_ID, PYMT_STAT_ID, APPT_ID) VALUES (?, ?, ?, ?, ?)");
        $stmt->bind_param("dsiis", $amount, $date, $methodId, $statusId, $apptId);
        $stmt->execute();
        $message = "Payment added successfully!";
    }
}

if (isset($_POST['update_payment'])) {
    $id = $_POST['paymt_id'];
    $amount = $_POST['paymt_amount_paid'];
    $date = $_POST['paymt_date'];
    $methodId = $_POST['pymt_meth_id'];
    $statusId = $_POST['pymt_stat_id'];
    $apptId = $_POST['appt_id'];

    $stmtCheck = $conn->prepare("SELECT 1 FROM APPOINTMENT WHERE APPT_ID=?");
    $stmtCheck->bind_param("i", $apptId);
    $stmtCheck->execute();
    if ($stmtCheck->get_result()->num_rows === 0) {
        $message = "Selected appointment does not exist!";
    } else {
        $stmt = $conn->prepare("UPDATE PAYMENT SET PAYMT_AMOUNT_PAID=?, PAYMT_DATE=?, PYMT_METH_ID=?, PYMT_STAT_ID=?, APPT_ID=? WHERE PAYMT_ID=?");
        $stmt->bind_param("dsiisi", $amount, $date, $methodId, $statusId, $apptId, $id);
        $stmt->execute();
        $message = "Payment updated successfully!";
    }
}

if ($currentRole === 'superadmin' && isset($_POST['delete_payment'])) {
    $stmt = $conn->prepare("DELETE FROM PAYMENT WHERE PAYMT_ID=?");
    $stmt->bind_param("i", $_POST['delete_payment']);
    $stmt->execute();
    $message = "Payment deleted successfully!";
}

$search = $_GET['search'] ?? '';
if ($search) {
    $stmt = $conn->prepare("SELECT p.*, pm.PYMT_METH_NAME, ps.PYMT_STAT_NAME 
                            FROM PAYMENT p
                            LEFT JOIN PAYMENT_METHOD pm ON p.PYMT_METH_ID = pm.PYMT_METH_ID
                            LEFT JOIN PAYMENT_STATUS ps ON p.PYMT_STAT_ID = ps.PYMT_STAT_ID
                            WHERE p.PAYMT_ID LIKE ? OR p.APPT_ID LIKE ? OR p.PAYMT_DATE LIKE ?
                            ORDER BY p.PAYMT_ID DESC");
    $like = "%$search%";
    $stmt->bind_param("sss", $like, $like, $like);
} else {
    $stmt = $conn->prepare("SELECT p.*, pm.PYMT_METH_NAME, ps.PYMT_STAT_NAME 
                            FROM PAYMENT p
                            LEFT JOIN PAYMENT_METHOD pm ON p.PYMT_METH_ID = pm.PYMT_METH_ID
                            LEFT JOIN PAYMENT_STATUS ps ON p.PYMT_STAT_ID = ps.PYMT_STAT_ID
                            ORDER BY p.PAYMT_ID DESC");
}
$stmt->execute();
$result = $stmt->get_result();
$paymentList = $result->fetch_all(MYSQLI_ASSOC);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Payment - Urban Medical Hospital</title>
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
            margin-left: -5px;
            padding: 40px 40px;
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
            color: green;
            font-weight: bold;
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
    <h1>Payment</h1>
    <?php if (isset($message)) echo "<div class='message'>$message</div>"; ?>

    <div class="action-bar">
        <div class="search-bar">
            <form method="GET">
                <input type="text" name="search" placeholder="Search payment..." value="<?= htmlspecialchars($search) ?>">
                <button class="btn btn-update" type="submit" style="height:60px;">Search</button>
            </form>
        </div>
        <button class="btn btn-add" onclick="showAddForm()">Add Payment</button>
    </div>

    <table>
        <tr>
            <th>ID</th>
            <th>Amount Paid</th>
            <th>Date</th>
            <th>Method</th>
            <th>Status</th>
            <th>Appointment</th>
            <th>Actions</th>
        </tr>
        <?php if ($paymentList): ?>
            <?php foreach ($paymentList as $p): ?>
            <tr>
                <td><?= $p['PAYMT_ID'] ?></td>
                <td>₱<?= number_format($p['PAYMT_AMOUNT_PAID'], 2) ?></td>
                <td><?= htmlspecialchars($p['PAYMT_DATE']) ?></td>
                <td><?= htmlspecialchars($p['PYMT_METH_NAME']) ?></td>
                <td><?= htmlspecialchars($p['PYMT_STAT_NAME']) ?></td>
                <td><?= htmlspecialchars($p['APPT_ID']) ?></td>
                <td>
                    <button class="btn btn-update" onclick="showUpdateForm(
                        '<?= $p['PAYMT_ID'] ?>',
                        '<?= htmlspecialchars($p['PAYMT_AMOUNT_PAID'], ENT_QUOTES) ?>',
                        '<?= htmlspecialchars($p['PAYMT_DATE'], ENT_QUOTES) ?>',
                        '<?= $p['PYMT_METH_ID'] ?>',
                        '<?= $p['PYMT_STAT_ID'] ?>',
                        '<?= $p['APPT_ID'] ?>'
                    )" style="height: 40px; width: 100px;">Edit</button>

                    <form method="POST" style="display:inline;" <?= $currentRole !== 'superadmin' ? "onsubmit=\"alert('Not allowed'); return false;\"" : "" ?>>
                        <input type="hidden" name="delete_payment" value="<?= $p['PAYMT_ID'] ?>">
                        <button class="btn btn-delete" style="height: 40px; width: 100px;">Delete</button>
                    </form>
                </td>
            </tr>
            <?php endforeach; ?>
        <?php else: ?>
            <tr><td colspan="7">No payments found.</td></tr>
        <?php endif; ?>
    </table>
</div>

<!-- Add Payment Form -->
<div id="addForm" class="add-form-container">
    <form method="POST">
        <h2>Add Payment</h2>
        
        <label>Amount Paid:</label>
        <input type="number" step="0.01" name="paymt_amount_paid" required>
        
        <label>Date:</label>
        <input type="date" name="paymt_date" required>
        
        <label>Method:</label>
        <select name="pymt_meth_id" id="add_method" required>
            <option value="">-- Select Method --</option>
        </select>
        
        <label>Status:</label>
        <select name="pymt_stat_id" id="add_status" required>
            <option value="">-- Select Status --</option>
        </select>
        
        <label>Appointment:</label>
        <select name="appt_id" required>
            <option value="">-- Select Appointment --</option>
            <?php foreach ($appts as $a): ?>
            <option value="<?= $a['APPT_ID'] ?>">#<?= $a['APPT_ID'] ?></option>
            <?php endforeach; ?>
        </select>
        
        <div class="form-actions">
            <button type="submit" name="add_payment" class="btn btn-add">Save</button>
            <button type="button" class="btn btn-delete" onclick="hideAddForm()">Cancel</button>
        </div>
    </form>
</div>

<!-- Update Payment Form -->
<div id="updateForm" class="add-form-container">
    <form method="POST">
        <h2>Update Payment</h2>
        
        <input type="hidden" name="paymt_id" id="update_id">
        
        <label>Amount Paid:</label>
        <input type="number" step="0.01" name="paymt_amount_paid" id="update_amount" required>
        
        <label>Date:</label>
        <input type="date" name="paymt_date" id="update_date" required>
        
        <label>Method:</label>
        <select name="pymt_meth_id" id="update_method" required>
            <option value="">-- Select Method --</option>
        </select>
        
        <label>Status:</label>
        <select name="pymt_stat_id" id="update_status" required>
            <option value="">-- Select Status --</option>
        </select>
        
        <label>Appointment:</label>
        <select name="appt_id" id="update_appt" required>
            <option value="">-- Select Appointment --</option>
            <?php foreach ($appts as $a): ?>
            <option value="<?= $a['APPT_ID'] ?>">#<?= $a['APPT_ID'] ?></option>
            <?php endforeach; ?>
        </select>
        
        <div class="form-actions">
            <button type="submit" name="update_payment" class="btn btn-add">Update</button>
            <button type="button" class="btn btn-delete" onclick="hideUpdateForm()">Cancel</button>
        </div>
    </form>
</div>

<div id="overlay" class="overlay" onclick="hideAddForm(); hideUpdateForm();"></div>

<script>
// Load payment methods and statuses when page loads
document.addEventListener('DOMContentLoaded', function() {
    loadPaymentMethods('add_method');
    loadPaymentMethods('update_method');
    loadPaymentStatuses('add_status');
    loadPaymentStatuses('update_status');
});

function showAddForm() {
    document.getElementById('overlay').classList.add('show');
    const f = document.getElementById('addForm');
    f.style.display = 'block';
    setTimeout(() => f.classList.add('show'), 10);
}

function hideAddForm() {
    const f = document.getElementById('addForm');
    f.classList.remove('show');
    document.getElementById('overlay').classList.remove('show');
    setTimeout(() => f.style.display = 'none', 400);
}

function showUpdateForm(id, amount, date, methodId, statusId, apptId) {
    document.getElementById('overlay').classList.add('show');
    const f = document.getElementById('updateForm');
    f.style.display = 'block';
    setTimeout(() => f.classList.add('show'), 10);
    
    document.getElementById('update_id').value = id;
    document.getElementById('update_amount').value = amount;
    document.getElementById('update_date').value = date;
    document.getElementById('update_appt').value = apptId;
    
    // Wait for dropdowns to load, then set selected values
    setTimeout(() => {
        document.getElementById('update_method').value = methodId;
        document.getElementById('update_status').value = statusId;
    }, 300);
}

function hideUpdateForm() {
    const f = document.getElementById('updateForm');
    f.classList.remove('show');
    document.getElementById('overlay').classList.remove('show');
    setTimeout(() => f.style.display = 'none', 400);
}

// Function to load payment methods from payment_method.php
function loadPaymentMethods(dropdownID) {
    fetch("payment_method.php")
        .then(response => response.json())
        .then(data => {
            const dropdown = document.getElementById(dropdownID);
            const currentValue = dropdown.value; // Preserve current selection
            dropdown.innerHTML = '<option value="">-- Select Method --</option>';

            data.forEach(method => {
                let opt = document.createElement("option");
                opt.value = method.id;
                opt.textContent = method.name;
                if (currentValue && currentValue == method.id) {
                    opt.selected = true;
                }
                dropdown.appendChild(opt);
            });
        })
        .catch(error => {
            console.error('Error loading payment methods:', error);
        });
}

// Function to load payment statuses from payment_status.php
function loadPaymentStatuses(dropdownID) {
    fetch("payment_status.php")
        .then(response => response.json())
        .then(data => {
            const dropdown = document.getElementById(dropdownID);
            const currentValue = dropdown.value; // Preserve current selection
            dropdown.innerHTML = '<option value="">-- Select Status --</option>';

            data.forEach(status => {
                let opt = document.createElement("option");
                opt.value = status.id;
                opt.textContent = status.name;
                if (currentValue && currentValue == status.id) {
                    opt.selected = true;
                }
                dropdown.appendChild(opt);
            });
        })
        .catch(error => {
            console.error('Error loading payment statuses:', error);
        });
}
</script>

</div>
<footer>
    <h2>&copy; 2025 Urban Medical Hospital. All Rights Reserved.</h2>
</footer>
</body>
</html>