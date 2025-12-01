<?php
session_start();
require_once __DIR__ . "/../../central_data/classes/Specialization.php";

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

$specObj = new Specialization();

if (isset($_POST['add_spec']) && $currentRole === 'Superadmin') {
    $data = ['spec_name' => $_POST['spec_name']];
    $specObj->add_spec($currentRole, $data);
    header("Location: specialization.php?added=1");
    exit;
}

if (isset($_POST['update_spec']) && in_array($currentRole, ['Superadmin', 'Staff'])) {
    $id = $_POST['spec_id'];
    $data = ['spec_name' => $_POST['spec_name']];
    $specObj->update_spec($currentRole, $id, $data);
    header("Location: specialization.php?updated=1");
    exit;
}

if (isset($_POST['delete_spec']) && $currentRole === 'Superadmin') {
    $msg = $specObj->delete_spec($currentRole, $_POST['delete_spec']);
    echo "<script>alert('".addslashes($msg)."');</script>";
    if ($msg === "Specialization deleted successfully!") {
        header("Location: specialization.php?deleted=1");
        exit;
    }
}

$search = $_GET['search'] ?? '';
if (!empty(trim($search))) {
    $specializationList = $specObj->search_special(trim($search));
} else {
    $specializationList = $specObj->get_all($currentRole);
}

if (!is_array($specializationList)) {
    $specializationList = [];
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Specialization - Urban Medical Hospital</title>
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
    <h1>Specialization</h1>
    <?php if (isset($_GET['added'])) echo "<div class='message'>Specialization added successfully!</div>"; ?>
    <?php if (isset($_GET['updated'])) echo "<div class='message'>Specialization updated successfully!</div>"; ?>
    <?php if (isset($_GET['deleted'])) echo "<div class='message'>Specialization deleted successfully!</div>"; ?>

    <div class="action-bar">
        <div class="search-bar">
            <form method="GET">
                <input type="text" name="search" placeholder="Search specialization..." value="<?= htmlspecialchars($search) ?>">
                <button class="btn btn-update" type="submit">Search</button>
            </form>
        </div>
        <?php if ($currentRole === 'Superadmin'): ?>
            <button class="btn btn-add" onclick="showAddForm()" style="width: 160px;">Add Specialization</button>
        <?php endif; ?>
    </div>

    <table>
        <tr>
            <th>ID</th>
            <th>Specialization Name</th>
            <th>Created At</th>
            <th>Updated At</th>
            <th>Actions</th>
        </tr>

        <?php if (!empty($specializationList) && is_array($specializationList)): ?>
            <?php foreach ($specializationList as $spec): ?>
                <tr>
                    <td><?= htmlspecialchars($spec['SPEC_ID'] ?? '') ?></td>
                    <td><?= htmlspecialchars($spec['SPEC_NAME'] ?? '') ?></td>
                    <td><?= htmlspecialchars($spec['SPEC_CREATED_AT'] ?? '') ?></td>
                    <td><?= htmlspecialchars($spec['SPEC_UPDATED_AT'] ?? '') ?></td>
                    <td>
                        <a href="view_doctors_by_spec.php?spec_id=<?= $spec['SPEC_ID'] ?>" 
                        class="btn btn-update" 
                        style="height: 60px; width: 110px; background-color: #28a745; text-decoration: none; font-weight: 100; margin-right: 10px;">
                        Browse
                        </a>
                        <button class="btn btn-update"
                            onclick="showUpdateForm('<?= $spec['SPEC_ID'] ?? '' ?>','<?= htmlspecialchars($spec['SPEC_NAME'] ?? '', ENT_QUOTES) ?>')" style="height: 40px; width: 110px;">Edit</button>
                        <?php if ($currentRole === 'Superadmin'): ?>
                        <form method="POST" style="display:inline;" onsubmit="return confirm('Delete this specialization?');">
    <input type="hidden" name="delete_spec" value="<?= $spec['SPEC_ID'] ?>">
    <button type="submit" class="btn btn-delete" style="height: 40px; width: 110px;">Delete</button>
</form>

                        <?php endif; ?>
                    </td>
                </tr>
            <?php endforeach; ?>
        <?php else: ?>
            <tr><td colspan="5">No specialization found.</td></tr>
        <?php endif; ?>
    </table>
</div>

<div id="addForm" class="add-form-container">
    <form method="POST">
        <h2>Add Specialization</h2>
        <label>Name:</label>
        <input type="text" name="spec_name" required>
        <div style="display:flex;justify-content:space-between;margin-top:20px;">
            <button type="submit" name="add_spec" class="btn btn-add">Save</button>
            <button type="button" class="btn btn-delete" onclick="hideAddForm()" style="height: 60px;">Cancel</button>
        </div>
    </form>
</div>

<div id="updateForm" class="add-form-container">
    <form method="POST">
        <h2>Update Specialization</h2>
        <input type="hidden" name="spec_id" id="update_id">
        <label>Name:</label>
        <input type="text" name="spec_name" id="update_name" required>
        <div style="display:flex;justify-content:space-between;margin-top:20px;">
            <button type="submit" name="update_spec" class="btn btn-add">Update</button>
            <button type="button" class="btn btn-delete" onclick="hideUpdateForm()" style="height: 60px;">Cancel</button>
        </div>
    </form>
</div>

<div id="overlay" class="overlay" onclick="hideAddForm(); hideUpdateForm();"></div>

<footer>
    <h2>&copy; 2025 Urban Medical Hospital by Hollys Group. All Rights Reserved.</h2>
</footer>

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
function showUpdateForm(id, name) {
    const f = document.getElementById('updateForm');
    document.getElementById('overlay').classList.add('show');
    f.style.display = 'block';
    setTimeout(()=>f.classList.add('show'),10);
    document.getElementById('update_id').value = id;
    document.getElementById('update_name').value = name;
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
