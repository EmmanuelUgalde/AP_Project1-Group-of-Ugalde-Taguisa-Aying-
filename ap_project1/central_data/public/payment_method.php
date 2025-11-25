<?php 
session_start();
require_once __DIR__ . "/../../central_data/classes/Payment_Method.php";

if (!isset($_SESSION['role'])) {
    echo "<div style='text-align:center; margin-top:200px; font-family: Port Lligat Slab, serif;'>
            <h1 style='color:red;'>Access Denied</h1>
            <p>You must be logged in to access this page.</p>
          </div>";
    exit;
}

$currentRole = ucfirst(strtolower($_SESSION['role']));

if (!in_array($currentRole, ['Superadmin', 'Staff'])) {
    echo "<div style='
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
        <p style='color: black;'>Only Superadmin or Staff can access this page.</p>
    </div>";
    exit;
}

$pay_methObj = new Pay_Method();

if (isset($_POST['add_paymeth'])) {
    $msg = $pay_methObj->add_paymeth($currentRole, ['pymt_meth_name' => $_POST['pymt_meth_name']]);
    header("Location: payment_method.php?msg=".urlencode($msg));
    exit;
}

if (isset($_POST['update_paymeth'])) {
    $id = $_POST['pymt_meth_id'];
    $msg = $pay_methObj->update_paymeth($currentRole, $id, ['pymt_meth_name' => $_POST['pymt_meth_name']]);
    header("Location: payment_method.php?msg=".urlencode($msg));
    exit;
}

if (isset($_POST['delete_paymeth']) && $currentRole === 'Superadmin') {
    $id = $_POST['delete_paymeth'];
    $msg = $pay_methObj->delete_paymeth($currentRole, $id);
    header("Location: payment_method.php?msg=".urlencode($msg));
    exit;
}

$search = $_GET['search'] ?? '';
if (!empty(trim($search))) {
    $payMethList = $pay_methObj->search_pay_meth($currentRole, trim($search));
} else {
    $payMethList = $pay_methObj->get_all($currentRole);
}

?>

<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Payment Methods - Urban Medical Hospital</title>
<style>
body {
    margin: 0;
    font-family: 'Port Lligat Slab', serif;
    background-color: #FFF9F9;
}
.container-page {
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
.search-bar input {
    padding: 15px 12px;
    width: 250px;
    border: 1px solid #ccc;
    border-radius: 5px;
}
.btn {
    padding: 8px 14px;
    border: none;
    border-radius: 5px;
    cursor: pointer;
    font-weight: bold;
}
.btn-add { background-color: #28a745; color: white; width: 150px; height: 60px; font-size: 20px; }
.btn-update { background-color: #007bff; color: white; width: 150px; height: 60px; font-size: 20px; }
.btn-delete { background-color: #dc3545; color: white; width: 150px; height: 50px; font-size: 20px; }
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
.message {
    margin: 10px 0;
    color: green;
    font-weight: bold;
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

.add-form-container input {
    width: 100%;
    padding: 10px;
    margin-top: 5px;
    border: 1px solid #ccc;
    border-radius: 5px;
}

footer {
        width: 100%;
        background-color: #0041c7;
        color: white;
        padding: 30px 50px;
        display: flex;
        justify-content: center;
        align-items: center;
        margin-top: 199px;
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
    <h1>Payment Methods</h1>
    <?php if (isset($_GET['added'])) echo "<div class='message'>Payment Method added successfully!</div>"; ?>
    <?php if (isset($_GET['updated'])) echo "<div class='message'>Payment Method updated successfully!</div>"; ?>
    <?php if (isset($_GET['deleted'])) echo "<div class='message'>Payment Method deleted successfully!</div>"; ?>

    <div class="action-bar">
        <?php if ($currentRole === 'Superadmin' || $currentRole === 'Staff'): ?>
        <div class="search-bar">
            <form method="GET">
                <input type="text" name="search" placeholder="Search payment method..." value="<?= htmlspecialchars($search) ?>">
                <button class="btn btn-update" type="submit">Search</button>
            </form>
        </div>
    <?php endif; ?>
        <?php if ($currentRole === 'Superadmin' || $currentRole === 'Staff'): ?>
            <button class="btn btn-add" onclick="showAddForm()" style="width: 160px;">Add Payment Method</button>
        <?php endif; ?>
    </div>

    <table>
        <tr>
            <th>Payment Method ID</th>
            <th>Payment Method Name</th>
            <th>Created At</th>
            <th>Updated At</th>
            <th>Actions</th>
        </tr>

        <?php if (!empty($payMethList) && is_array($payMethList)): ?>
            <?php foreach ($payMethList as $pymeth): ?>
                <tr>
                    <td><?= htmlspecialchars($pymeth['PYMT_METH_ID'] ?? '') ?></td>
                    <td><?= htmlspecialchars($pymeth['PYMT_METH_NAME'] ?? '') ?></td>
                    <td><?= htmlspecialchars($pymeth['PYMT_METH_CREATED_AT'] ?? '') ?></td>
                    <td><?= htmlspecialchars($pymeth['PYMT_METH_UPDATED_AT'] ?? '') ?></td>
                    <td>
    <button class="btn btn-update"
        onclick="showUpdateForm(
            '<?= $pymeth['PYMT_METH_ID'] ?>',
            '<?= htmlspecialchars($pymeth['PYMT_METH_NAME'], ENT_QUOTES) ?>'
        )"
        style="height: 40px; width: 100px;">
        Edit
    </button>

    <?php if ($currentRole === 'Superadmin'): ?>
    <form method="POST" style="display:inline;" 
          onsubmit="return confirm('Delete this payment method?');">
        <input type="hidden" name="delete_paymeth" 
               value="<?= $pymeth['PYMT_METH_ID'] ?>">
        <button type="submit" class="btn btn-delete" 
                style="height: 40px; width: 110px;">
            Delete
        </button>
    </form>
    <?php endif; ?>
</td>

                </tr>
            <?php endforeach; ?>
        <?php else: ?>
            <tr><td colspan="5">No payment method found.</td></tr>
        <?php endif; ?>
    </table>
</div>

<div id="addForm" class="add-form-container">
        <form method="POST" action="<?= htmlspecialchars($_SERVER['PHP_SELF']) ?>">
            <h2>Add New Payment Method</h2>

            <label>Payment Method:</label>
            <input type="text" name="pymt_meth_name" required>

            <div class="form-actions">
                <button type="submit" name="add_paymeth" class="btn btn-add" style="margin-top: 20px; margin-right: 45px;">Save</button>
                <button type="button" class="btn btn-delete" onclick="hideAddForm()" style="height: 60px;">Cancel</button>
            </div>
        </form>
</div>

<div id="updateForm" class="add-form-container">
        <form method="POST" action="">
            <h2>Update Payment Method</h2>
            <input type="hidden" name="pymt_meth_id" id="update_id">

            <label>Payment Method:</label>
            <input type="text" name="pymt_meth_name" id="update_paymeth">

            <div class="form-actions">
                <button type="submit" name="update_paymeth" class="btn btn-add" style="margin-top: 20px;">Update</button>
                <button type="button" class="btn btn-delete" onclick="hideUpdateForm()" style="height: 60px; margin-left: 45px;">Cancel</button>
            </div>
        </form>
    </div>

    <div id="overlay" class="overlay" onclick="hideAddForm()"></div>

</div>

<footer>
        <h2>&copy; 2025 Urban Medical Hospital by Hollys Group. All Rights Reserved.</h2>
    </footer>

<script>
function showAddForm() {
    const overlay = document.getElementById('overlay');
    const addForm = document.getElementById('addForm');

    overlay.classList.add('show');
    addForm.style.display = 'block';
    setTimeout(() => addForm.classList.add('show'), 10);
}

function hideAddForm() {
    const overlay = document.getElementById('overlay');
    const addForm = document.getElementById('addForm');

    addForm.classList.remove('show');
    overlay.classList.remove('show');
    setTimeout(() => addForm.style.display = 'none', 400);
}

function showUpdateForm(id, paymethName) {
    const overlay = document.getElementById('overlay');
    const updateForm = document.getElementById('updateForm');

    overlay.classList.add('show');
    updateForm.style.display = 'block';
    setTimeout(() => updateForm.classList.add('show'), 10);

    document.getElementById('update_id').value = id;
    document.getElementById('update_paymeth').value = paymethName;
}

function hideUpdateForm() {
    const overlay = document.getElementById('overlay');
    const updateForm = document.getElementById('updateForm');

    updateForm.classList.remove('show');
    overlay.classList.remove('show');
    setTimeout(() => updateForm.style.display = 'none', 400);
}

document.getElementById('overlay').addEventListener('click', function() {
    if(document.getElementById('addForm').classList.contains('show')) hideAddForm();
    if(document.getElementById('updateForm').classList.contains('show')) hideUpdateForm();
});
</script>

</body>
</html>