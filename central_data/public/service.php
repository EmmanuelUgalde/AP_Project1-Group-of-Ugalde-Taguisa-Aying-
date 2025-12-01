<?php
session_start();
require_once __DIR__ . "/../../central_data/classes/Service.php";

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
        <p style='color: black;'>Only SuperAdmin or Staff can access this page.</p>
    </div>";
    exit;
}

$servObj = new Service();

if (isset($_POST['add_service']) && in_array(strtolower($currentRole), ['superadmin', 'staff'])) {
    $data = [
        'serv_name' => $_POST['serv_name'],
        'serv_description' => $_POST['serv_description'],
        'serv_price' => $_POST['serv_price']
    ];

    $result = $servObj->add_service(strtolower($currentRole), $data);

if ($result["success"]) {
    header("Location: service.php?added=1");
    exit;
} else {
    die("<h2 style='color:red; text-align:center; margin-top:100px;'>ERROR ADDING SERVICE:<br>" . $result["message"] . "</h2>");
}
}

if (isset($_POST['update_service']) && in_array(strtolower($currentRole), ['superadmin', 'staff'])) {
    $id = $_POST['serv_id'];
    $data = [
        'serv_name' => $_POST['serv_name'],
        'serv_description' => $_POST['serv_description'],
        'serv_price' => $_POST['serv_price']
    ];

    $servObj->update_service(strtolower($currentRole), $id, $data);
    header("Location: service.php?updated=1");
    exit;
}

if (strtolower($currentRole) === 'superadmin' && isset($_POST['delete_service'])) {
    $servObj->delete_service(strtolower($currentRole), $_POST['delete_service']);
    header("Location: service.php?deleted=1");
    exit;
}

$allServices = $servObj->get_all(strtolower($currentRole));

$search = $_GET['search'] ?? '';
$services = [];

if ($search) {
    foreach ($allServices as $sv) {
        if (
            stripos($sv['SERV_NAME'], $search) !== false ||
            stripos($sv['SERV_DESCRIPTION'], $search) !== false
        ) {
            $services[] = $sv;
        }
    }
} else {
    $services = $allServices;
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Service - Urban Medical Hospital</title>
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

.btn btn-add {
background-color: #28a745; color: white; width: 150px; height: 60px; font-size: 20px;
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

.form-actions {
    display: flex; 
    justify-content: space-between; 
    margin-top: 15px; 
    gap: 20px;
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
    <h1>Service</h1>
    <?php if (isset($_GET['added'])) echo "<div class='message'>Service added successfully!</div>"; ?>
    <?php if (isset($_GET['updated'])) echo "<div class='message'>Service updated successfully!</div>"; ?>
    <?php if (isset($_GET['deleted'])) echo "<div class='message'>Service deleted successfully!</div>"; ?>

    <div class="action-bar">
        <div class="search-bar">
            <form method="GET">
                <input type="text" name="search" placeholder="Search service..." value="<?= htmlspecialchars($search) ?>">
                <button class="btn btn-update" type="submit">Search</button>
            </form>
        </div>
        <?php if ($currentRole === 'Superadmin' || $currentRole === 'Staff'): ?>
            <button class="btn btn-add" onclick="showAddForm()" style="width: 160px;">Add Service</button>
        <?php endif; ?>
    </div>

    <table>
    <tr>
        <th>ID</th><th>Name</th><th>Description</th><th>Price</th><th>Actions</th>
    </tr>

    <?php if (!empty($services)): ?>
        <?php foreach ($services as $sv): ?>
            <tr>
                <td><?= htmlspecialchars($sv['SERV_ID']) ?></td>
                <td><?= htmlspecialchars($sv['SERV_NAME']) ?></td>
                <td><?= htmlspecialchars($sv['SERV_DESCRIPTION']) ?></td>
                <td><?= number_format($sv['SERV_PRICE'], 2) ?></td>
                <td>
                    <a href="view_appoint_by_service.php?service_id=<?= $sv['SERV_ID'] ?>" class="btn btn-add" style="text-decoration:none; height: 60px;">View Appointments</a>

                    <button class="btn btn-update"
                        onclick="showUpdateForm(
                            '<?= $sv['SERV_ID'] ?>',
                            '<?= htmlspecialchars($sv['SERV_NAME'], ENT_QUOTES) ?>',
                            '<?= htmlspecialchars($sv['SERV_DESCRIPTION'], ENT_QUOTES) ?>',
                            '<?= htmlspecialchars($sv['SERV_PRICE'], ENT_QUOTES) ?>'
                        )"
                        style="height: 40px; width: 100px;">
                        Edit
                    </button>

                    <?php if ($currentRole === 'Superadmin'): ?>
                        <form method="POST" style="display:inline" onsubmit="return confirm('Delete this service?');">
                            <input type="hidden" name="delete_service" value="<?= $sv['SERV_ID'] ?>">
                            <button class="btn btn-delete" type="submit" style="width: 100px; height: 40px;">Delete</button>
                        </form>
                    <?php endif; ?>
                </td>
            </tr>
        <?php endforeach; ?>

    <?php else: ?>
        <tr><td colspan="5">No services found.</td></tr>
    <?php endif; ?>

</table>
</div>

<div id="addForm" class="add-form-container">
  <form method="POST">
    <h2>Add Service</h2>
    <label>Name</label><input type="text" name="serv_name" required><br>
    <label>Description</label><textarea name="serv_description" rows="4" style="width:100%" required></textarea><br>
    <label>Price</label><input type="number" step="0.01" name="serv_price" required><br>
    <div class="form-actions">
      <button name="add_service" class="btn btn-add" type="submit">Save</button>
      <button type="button" class="btn btn-delete" onclick="hideAddForm()" style="height: 60px;">Cancel</button>
    </div>
  </form>
</div>

<div id="updateForm" class="add-form-container">
  <form method="POST">
    <h2>Update Service</h2>
    <input type="hidden" name="serv_id" id="update_id">
    <label>Name</label><input type="text" name="serv_name" id="update_name" required><br>
    <label>Description</label><textarea name="serv_description" id="update_desc" rows="4" style="width:100%" required></textarea><br>
    <label>Price</label><input type="number" step="0.01" name="serv_price" id="update_price" required><br>
    <div class="form-actions">
      <button name="update_service" class="btn btn-add" type="submit">Update</button>
      <button type="button" class="btn btn-delete" onclick="hideUpdateForm()" style="height: 60px;">Cancel</button>
    </div>
  </form>
</div>

<div id="overlay" class="overlay" onclick="hideAddForm(); hideUpdateForm();"></div>
<footer><h2>&copy; 2025 Urban Medical Hospital by Hollys Group. All Rights Reserved.</h2></footer>

<script>
function showAddForm() {
    const f = document.getElementById('addForm');
    document.getElementById('overlay').classList.add('show');
    f.style.display = 'block';
    setTimeout(()=>f.classList.add('show'),10);
}
function hideAddForm() {
    const f = document.getElementById('addForm');
    document.getElementById('overlay').classList.remove('show');
    f.classList.remove('show');
    setTimeout(()=>f.style.display='none',400);
}
function showUpdateForm(id, days, start, end, doc_id) {
    const f = document.getElementById('updateForm');
    document.getElementById('overlay').classList.add('show');
    f.style.display = 'block';
    setTimeout(()=>f.classList.add('show'),10);

    document.getElementById('update_id').value   = id;
    document.getElementById('update_days').value = days;
    document.getElementById('update_start').value = start;
    document.getElementById('update_end').value   = end;
    document.getElementById('update_doc').value   = doc_id;
}

function hideUpdateForm() {
    const f = document.getElementById('updateForm');
    document.getElementById('overlay').classList.remove('show');
    f.classList.remove('show');
    setTimeout(()=>f.style.display='none',400);
}
</script>
</body>
</html>
