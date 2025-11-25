<?php
session_start();
require_once __DIR__ . "/../../central_data/classes/Status.php";

if (!isset($_SESSION['role'])) {
    echo "
    <div style='
        display: flex;
        justify-content: center;
        align-items: center;
        height: calc(100vh - 60px); /* subtract header height if fixed */
        flex-direction: column;
        text-align: center;
        font-family: \"Port Lligat Slab\", serif;
        color: red;
        font-size: 100px;
    '>
        <h1>Access Denied</h1>
        <p style='color: black;'>You must be logged in to access this page.</p>
    </div>
    ";
    exit;
}

$currentRole = ucfirst(strtolower($_SESSION['role']));

if (!in_array($currentRole, ['Superadmin', 'Staff'])) {
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

$statusObj = new Status();

if (isset($_POST['add_status'])) {
    $data = ['status_name' => $_POST['status_name']];
    $statusObj->add_status($currentRole, $data);
    $message = "Status updated successfully.";
    header("Location: status.php?added=1");
    exit;
}

if (isset($_POST['update_status'])) {
    $id = $_POST['status_id'];
    $data = ['status_name' => $_POST['status_name']];
    $statusObj->update_status(strtolower($currentRole), $id, $data);
    header("Location: status.php?updated=1");
    exit;
}

if ($currentRole === 'Superadmin' && isset($_POST['delete_status'])) {
    $result = $statusObj->delete_status(strtolower($currentRole), $_POST['delete_status']);
    if ($result === "Success") {
        header("Location: status.php?msg=deleted");
        exit;
    }
    header("Location: status.php?msg=" . urlencode($result));
    exit;
}



$search = $_GET['search'] ?? '';

if (!empty(trim($search))) {
    $statuses = $statusObj->search_status_by_name(strtolower($currentRole), trim($search));
} else {
    $statuses = $statusObj->get_all_with_appointments(strtolower($currentRole));
}

if (!is_array($statuses)) {
    $statuses = [];
}

if (isset($_POST['status_id'], $_POST['status_name'])) {
    $statusObj = new Status();
    $id = $_POST['status_id'];
    $data = ['status_name' => $_POST['status_name']];
    $statusObj->update_status(strtolower($currentRole), $id, $data);
    echo json_encode(['success' => true]);
    exit;
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width,initial-scale=1">
<title>Status - Urban Medical Hospital</title>
<style>
body {
    margin: 0;
    font-family: 'Port Lligat Slab', serif;
    background: #FFF9F9;
}
.container-page {
    padding: 70px;
}
h1 {
    font-size: 80px;
    margin-top: 120px;
    color: #000;
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
    padding: 15px 12px;
    width: 250px;
    border: 1px solid #ccc;
    border-radius: 5px;
    margin-left: -5px;
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
    font-weight: bold;
    cursor: pointer;
}
.btn-add {
    background: #28a745;
    color: #fff;
    width: 150px;
    height: 60px;
}
.btn-update {
    background: #007bff;
    color: #fff;
    width: 150px;
    height: 70px;
}
.btn-delete {
    background: #dc3545;
    color: #fff;
    width: 150px;
    height: 50px;
}
table {
    width: 100%;
    border-collapse: collapse;
    margin-top: 25px;
    background: #fff;
}
th, td {
    padding: 12px;
    text-align: center;
    border-bottom: 1px solid #ddd;
    font-size: 18px;
}
th {
    background: #f1f1f1;
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

@keyframes slideDownIn {
  from { transform: translate(-50%, -60%) scale(0.95); opacity: 0; }
  to   { transform: translate(-50%, -50%) scale(1);    opacity: 1; }
}

@keyframes slideDownOut {
  from { transform: translate(-50%, -50%) scale(1);    opacity: 1; }
  to   { transform: translate(-50%, -60%) scale(0.95); opacity: 0; }
}

.form-actions {
    display: flex;
    justify-content: space-between;
    margin-top: 20px;
}

footer {
        width: 100%;
        background-color: #0041c7;
        color: white;
        padding: 30px 50px;
        display: flex;
        justify-content: center;
        align-items: center;
        margin-top: 202px;
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
    <h1>Status</h1>
    <?php if(isset($message)) echo "<div style='color:green;font-weight:bold;margin:10px 0;'>".htmlspecialchars($message)."</div>"; ?>
    <div class="action-bar">
        <div class="search-bar">
            <form method="GET">
                <input type="text" name="search" placeholder="Search status..." value="<?= htmlspecialchars($search) ?>">
                <button class="btn btn-update" type="submit" style="height:60px; font-size: 20px;">Search</button>
            </form>
        </div>
        <button class="btn btn-add" onclick="showAddForm()" style="font-size: 20px;">Add Status</button>
    </div>

<?php
if (isset($_GET['added'])) echo "<div class='message'>Status added successfully!</div>";
if (isset($_GET['updated'])) echo "<div class='message'>Status updated successfully!</div>";
if (isset($_GET['deleted'])) echo "<div class='message'>Status deleted successfully!</div>";
if (isset($_GET['msg'])): ?>
<script>
    let msg = "<?= htmlspecialchars($_GET['msg']) ?>";
    if (msg !== "deleted") {
        alert(msg);
    }
</script>
<?php endif; ?>


    <table>
    <tr>
        <th>Status ID</th>
        <th>Appointment ID</th>
        <th>Name</th>
        <th>Created At</th>
        <th>Updated At</th>
        <th>Actions</th>
    </tr>
    <?php if (!empty($statuses)): ?>
        <?php foreach ($statuses as $s): ?>
        <tr>
            <td><?= htmlspecialchars($s['STATUS_ID']) ?></td>
            <td><?= htmlspecialchars($s['APPT_ID'] ?? 'N/A') ?></td>
            <td>
                <select name="status_name" data-id="<?= $s['STATUS_ID'] ?>">
                    <?php
                        $options = ['Scheduled', 'Completed', 'Cancelled'];
                        foreach ($options as $opt) {
                            $selected = ($s['STATUS_NAME'] === $opt) ? 'selected' : '';
                            echo "<option value='{$opt}' {$selected}>{$opt}</option>";
                        }
                    ?>
                </select>
            </td>
            <td><?= htmlspecialchars($s['STATUS_CREATED_AT']) ?></td>
            <td><?= htmlspecialchars($s['STATUS_UPDATED_AT']) ?></td>
            <td>
                <?php if ($currentRole === 'Superadmin'): ?>
                <form method="POST" style="display:inline;" onsubmit="return confirm('Delete this status?');">
                    <input type="hidden" name="delete_status" value="<?= $s['STATUS_ID'] ?>">
                    <button class="btn btn-delete" type="submit" style="height: 40px; width: 100px; font-size: 20px;">Delete</button>
                </form>
                <?php endif; ?>
            </td>
        </tr>
        <?php endforeach; ?>
    <?php else: ?>
        <tr>
            <td colspan="6" style="text-align:center;">No statuses found.</td>
        </tr>
    <?php endif; ?>
</table>
</div>

<div id="addForm" class="add-form-container">
  <form method="POST" action="">
    <h2>Add Status</h2>
    <label>Appointment ID</label>
    <input type="number" name="appt_id" required placeholder="Enter Appointment ID">
            
    <br>

    <label style="margin-bottom: 10px;">Status Name</label>
    <select name="status_name" style="height: 30px; width: 150px; font-size: 15px;" required>
      <option value="Scheduled">Scheduled</option>
      <option value="Completed">Completed</option>
      <option value="Cancelled">Cancelled</option>
    </select>

    <div class="form-actions">
      <button name="add_status" class="btn btn-add" type="submit" style="height: 60px; width: 160px; font-size: 20px;">Save</button>
      <button type="button" class="btn btn-delete" onclick="hideAddForm()" style="height: 60px; width: 160px; font-size: 20px;">Cancel</button>
    </div>
  </form>
</div>

<div id="updateForm" class="add-form-container">
  <form method="POST" action="">
    <h2>Update Status</h2>
    <input type="hidden" name="stat_id" id="update_id">

    <label>Appointment ID</label>
    <input type="number" name="appt_id" id="update_appt_id" required placeholder="Enter Appointment ID">
    
    <label>Status Name</label>
    <select name="status_name" id="update_status_name" required>
      <option value="Scheduled">Scheduled</option>
      <option value="Completed">Completed</option>
      <option value="Cancelled">Cancelled</option>
    </select>

    <div class="form-actions">
      <button name="update_status" class="btn btn-add" type="submit">Update</button>
      <button type="button" class="btn btn-delete" onclick="hideUpdateForm()">Cancel</button>
    </div>
  </form>
</div>


<div id="overlay" class="overlay" onclick="hideAddForm(); hideUpdateForm();"></div>
<footer>
    <h2>&copy; 2025 Urban Medical Hospital by Hollys Group. All Rights Reserved.</h2>
</footer>

<script>
document.querySelectorAll('select[name="status_name"]').forEach(select => {
    select.addEventListener('change', function() {
        const statusId = this.dataset.id;
        const statusName = this.value;

        fetch('status.php', {
            method: 'POST',
            headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
            body: `status_id=${statusId}&status_name=${statusName}`
        })
        .then(res => res.json())
        .then(data => {
            if (data.success) {
                alert('Status updated successfully');
                // Optionally, update "Updated At" cell here
            } else {
                alert('Error: ' + (data.error || 'Unknown error'));
            }
        });
    });
});

function showAddForm(){
  document.getElementById('overlay').classList.add('show');
  const f=document.getElementById('addForm');
  f.style.display='block';
  setTimeout(()=>f.classList.add('show'),10);
}
function hideAddForm(){
  const f=document.getElementById('addForm');
  document.getElementById('overlay').classList.remove('show');
  f.classList.remove('show');
  setTimeout(()=>f.style.display='none',350);
}
function showUpdateForm(id,name){
  document.getElementById('overlay').classList.add('show');
  const f=document.getElementById('updateForm');
  f.style.display='block';
  setTimeout(()=>f.classList.add('show'),10);
  document.getElementById('update_id').value=id;
  document.getElementById('update_name').value=name;
}
function hideUpdateForm(){
  const f=document.getElementById('updateForm');
  document.getElementById('overlay').classList.remove('show');
  f.classList.remove('show');
  setTimeout(()=>f.style.display='none',350);
}
</script>
</body>
</html>
