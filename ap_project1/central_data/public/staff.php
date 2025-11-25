<?php
session_start();
require_once __DIR__ . "/../../central_data/classes/Staff.php";

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

$currentRole = $_SESSION['role'];
$staffObj = new Staff();
$staffList = $staffObj->get_all($currentRole);

if (isset($_POST['add_staff'])) {
    $data = [
        'staff_first_name' => $_POST['staff_first_name'],
        'staff_last_name' => $_POST['staff_last_name'],
        'staff_middle_init' => $_POST['staff_middle_init'],
        'staff_contact_num' => $_POST['staff_contact_num'],
        'staff_email' => $_POST['staff_email']
    ];
    $staffObj->add_staff($currentRole, $data);

    header("Location: staff.php?added=1");
    exit;
}

if (isset($_POST['update_staff'])) {
    $id = $_POST['staff_id'];
    $data = [
        'staff_first_name' => $_POST['staff_first_name'],
        'staff_last_name' => $_POST['staff_last_name'],
        'staff_middle_init' => $_POST['staff_middle_init'],
        'staff_contact_num' => $_POST['staff_contact_num'],
        'staff_email' => $_POST['staff_email']
    ];
    $staffObj->update_staff($currentRole, $id, $data);

    header("Location: staff.php?updated=1");
    exit;
}

if (strtolower($currentRole) === 'superadmin' && isset($_POST['delete_staff'])) {
    $id = $_POST['staff_id'];
    $staffObj->delete_staff($currentRole, $id);
    
    header("Location: staff.php?deleted=1");
    exit;
}

if (isset($_GET['added'])) {
    $message = "Staff added successfully!";
}

if (isset($_GET['updated'])) {
    $message = "Staff updated successfully!";
}

if (isset($_GET['deleted'])) {
    $message = "Staff deleted successfully!";
}

$staffList = $staffObj->get_all($currentRole);

if (isset($_GET['search']) && !empty(trim($_GET['search']))) {
    $keyword = trim($_GET['search']);
    $staffList = $staffObj->search_staff($currentRole, $keyword);
} else {
    $staffList = $staffObj->get_all($currentRole);
}

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Staff - Urban Medical Hospital</title>
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
            cursor: pointer;
            font-weight: bold;
        }

        .btn-add { font-size: 20px; background-color: #28a745; color: white; width: 150px; height: 60px; margin-right: 15px;}
        .btn-update { font-size: 20px; background-color: #007bff; color: white; width: 150px; height:60px;}
        .btn-delete { font-size: 20px; background-color: #dc3545; color: white; width: 110px; height: 40px;}

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
        <h1>Staff</h1>

        <?php if (isset($message)) echo "<div class='message'>$message</div>"; ?>

        <div class="action-bar">
            <div class="search-bar">
                <form method="GET">
                    <input type="text" name="search" placeholder="Search staff...">
                    <button class="btn btn-update" type="submit">Search</button>
                </form>
            </div>

            <div>
                <button class="btn btn-add" onclick="showAddForm()">Add Staff</button>
            </div>
        </div>

        <table>
            <tr>
                <th>ID</th>
                <th>First Name</th>
                <th>Middle Initial</th>
                <th>Last Name</th>
                <th>Contact</th>
                <th>Email</th>
                <th>Actions</th>
            </tr>

            <?php if (!empty($staffList) && is_array($staffList)): ?>
                <?php foreach ($staffList as $staff): ?>
                    <tr>
                        <td><?= htmlspecialchars($staff['STAFF_ID']) ?></td>
                        <td><?= htmlspecialchars($staff['STAFF_FIRST_NAME']) ?></td>
                        <td><?= htmlspecialchars($staff['STAFF_MIDDLE_INIT']) ?></td>
                        <td><?= htmlspecialchars($staff['STAFF_LAST_NAME']) ?></td>
                        <td><?= htmlspecialchars($staff['STAFF_CONTACT_NUM']) ?></td>
                        <td><?= htmlspecialchars($staff['STAFF_EMAIL']) ?></td>
                        <td>
                            <button type="button" style="width: 100px; height: 40px;" class="btn btn-update" name="update"
          onclick="showUpdateForm(
            '<?= $staff['STAFF_ID'] ?>',
            '<?= htmlspecialchars($staff['STAFF_FIRST_NAME'], ENT_QUOTES) ?>',
            '<?= htmlspecialchars($staff['STAFF_MIDDLE_INIT'], ENT_QUOTES) ?>',
            '<?= htmlspecialchars($staff['STAFF_LAST_NAME'], ENT_QUOTES) ?>',
            '<?= htmlspecialchars($staff['STAFF_CONTACT_NUM'], ENT_QUOTES) ?>',
            '<?= htmlspecialchars($staff['STAFF_EMAIL'], ENT_QUOTES) ?>'
          )">Edit</button>
                            <form method="POST" style="display:inline;"
      onsubmit="return confirm('<?= $currentRole === 'Superadmin' ? 'Are you sure to delete this?' : 'You are not allowed to delete!' ?>');">
    <input type="hidden" name="staff_id" value="<?= $staff['STAFF_ID'] ?>">
    <input type="hidden" name="delete_staff" value="1">
    <button type="submit" class="btn btn-delete" style="width: 100px; height: 40px;">Delete</button>
</form>
                        </td>
                    </tr>
                <?php endforeach; ?>
            <?php else: ?>
                <tr><td colspan="7">No staff found.</td></tr>
            <?php endif; ?>
        </table>

        <div id="addForm" class="add-form-container">
    <form method="POST" action="">
        <h2>Add New Staff</h2>

        <label>First Name:</label>
        <input type="text" name="staff_first_name" required>

        <label>Middle Initial:</label>
        <input type="text" name="staff_middle_init" maxlength="1">

        <label>Last Name:</label>
        <input type="text" name="staff_last_name" required>

        <label>Contact Number:</label>
        <input type="text" name="staff_contact_num" required>

        <label>Email:</label>
        <input type="email" name="staff_email" required>

        <div class="form-actions">
            <button type="submit" name="add_staff" class="btn btn-add">Save</button>
            <button type="button" class="btn btn-delete" onclick="hideAddForm()" style="width: 150px; height: 60px;">Cancel</button>
        </div>
    </form>
</div>

 <div id="updateForm" class="add-form-container">
<form method="POST" action="">
    <h2>Update Staff</h2>
        <input type="hidden" name="staff_id" id="update_id">
        <label>First Name:</label><input type="text" name="staff_first_name" id="update_first" required>
        <label>Middle Initial:</label><input type="text" name="staff_middle_init" id="update_mid" maxlength="1">
        <label>Last Name:</label><input type="text" name="staff_last_name" id="update_last" required>
        <label>Contact Number:</label><input type="text" name="staff_contact_num" id="update_contact" required>
        <label>Email:</label><input type="email" name="staff_email" id="update_email" required>
<div class="form-actions">
  <button type="submit" name="update_staff" class="btn btn-add">Update</button>
  <button type="button" class="btn btn-delete" onclick="hideUpdateForm()" style="width: 150px; height: 60px; gap: -20px;">Cancel</button>
</div>
</form>
</div>
    </div>

    <div id="overlay" class="overlay" onclick="hideAddForm()"></div>

    <footer>
        <h2>&copy; 2025 Urban Medical Hospital by Hollys Group. All Rights Reserved.</h2>
    </footer>

<script>
  function showAddForm() {
    const form = document.getElementById('addForm');
    const overlay = document.getElementById('overlay');
    if (!form) return console.error("addForm not found");

    if (overlay) overlay.classList.add('show');
    form.classList.remove('hiding');
    form.style.display = 'block';
    setTimeout(() => form.classList.add('show'), 10);
  }

  function hideAddForm() {
    const form = document.getElementById('addForm');
    const overlay = document.getElementById('overlay');
    if (!form) return;

    if (overlay) overlay.classList.remove('show');
    form.classList.remove('show');
    form.classList.add('hiding');

    setTimeout(() => {
      form.classList.remove('hiding');
      form.style.display = 'none';
    }, 350);
  }

  function showUpdateForm(id, first, mid, last, contact, email) {
    const form = document.getElementById('updateForm');
    const overlay = document.getElementById('overlay');
    if (!form) return console.error("updateForm not found");

    if (overlay) overlay.classList.add('show');
    form.classList.remove('hiding');
    form.style.display = 'block';
    setTimeout(() => form.classList.add('show'), 10);

    document.getElementById('update_id').value = id;
    document.getElementById('update_first').value = first;
    document.getElementById('update_mid').value = mid;
    document.getElementById('update_last').value = last;
    document.getElementById('update_contact').value = contact;
    document.getElementById('update_email').value = email;
  }

  function hideUpdateForm() {
    const form = document.getElementById('updateForm');
    const overlay = document.getElementById('overlay');
    if (!form) return;

    if (overlay) overlay.classList.remove('show');
    form.classList.remove('show');
    form.classList.add('hiding');

    setTimeout(() => {
      form.classList.remove('hiding');
      form.style.display = 'none';
    }, 350);
  }

  function deleteStaff(staffId) {
    if (!confirm('Delete this staff?')) return;

    fetch('<?= $_SERVER['PHP_SELF'] ?>', {
        method: 'POST',
        headers: {'Content-Type': 'application/x-www-form-urlencoded'},
        body: 'delete_staff_id=' + staffId
    })
    .then(res => res.text())
    .then(() => {
        window.location.reload();
    })
    .catch(err => console.error(err));
}
</script>

</body>
</html>
