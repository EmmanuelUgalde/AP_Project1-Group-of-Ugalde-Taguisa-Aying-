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

$currentRole = ucfirst(strtolower($_SESSION['role']));

if (!in_array($currentRole, ['Superadmin', 'Patient'])) {
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
        <p style='color: black;'>Only SuperAdmin or Patient can access this page.</p>
    </div>
    ";
    exit;
}

$currentRole = strtolower($_SESSION['role']);

$db = new Database();
$conn = $db->getConn();

if (isset($_POST['add_patient'])) {
    $stmt = $conn->prepare("INSERT INTO PATIENT (PAT_FIRST_NAME, PAT_MIDDLE_INIT, PAT_LAST_NAME, PAT_DOB, PAT_GENDER, PAT_CONTACT_NUM, PAT_EMAIL, PAT_ADDRESS) VALUES (?, ?, ?, ?, ?, ?, ?, ?)");
    $stmt->bind_param("ssssssss",
        $_POST['pat_first_name'],
        $_POST['pat_middle_init'],
        $_POST['pat_last_name'],
        $_POST['pat_dob'],
        $_POST['pat_gender'],
        $_POST['pat_contact_num'],
        $_POST['pat_email'],
        $_POST['pat_address']
    );
    $stmt->execute();
    $message = "Patient added successfully!";
}

if (isset($_POST['update_patient'])) {
    $stmt = $conn->prepare("UPDATE PATIENT SET PAT_FIRST_NAME=?, PAT_MIDDLE_INIT=?, PAT_LAST_NAME=?, PAT_DOB=?, PAT_GENDER=?, PAT_CONTACT_NUM=?, PAT_EMAIL=?, PAT_ADDRESS=? WHERE PAT_ID=?");
    $stmt->bind_param("ssssssssi",
        $_POST['pat_first_name'],
        $_POST['pat_middle_init'],
        $_POST['pat_last_name'],
        $_POST['pat_dob'],
        $_POST['pat_gender'],
        $_POST['pat_contact_num'],
        $_POST['pat_email'],
        $_POST['pat_address'],
        $_POST['pat_id']
    );
    $stmt->execute();
    $message = "Patient updated successfully!";
}

if ($currentRole === 'superadmin' && isset($_POST['delete_patient'])) {
    $stmt = $conn->prepare("DELETE FROM PATIENT WHERE PAT_ID=?");
    $stmt->bind_param("i", $_POST['delete_patient']);
    $stmt->execute();
    header("Location: patient.php?deleted=1");
    exit;
}

if (isset($_GET['deleted'])) {
    $message = "Patient deleted successfully!";
}

$search = $_GET['search'] ?? '';
if ($search) {
    $stmt = $conn->prepare("SELECT * FROM PATIENT WHERE PAT_FIRST_NAME LIKE ? OR PAT_LAST_NAME LIKE ? OR PAT_EMAIL LIKE ?");
    $likeSearch = "%$search%";
    $stmt->bind_param("sss", $likeSearch, $likeSearch, $likeSearch);
} else {
    $stmt = $conn->prepare("SELECT * FROM PATIENT");
}

$stmt->execute();
$result = $stmt->get_result();
$patientList = $result->fetch_all(MYSQLI_ASSOC);
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

        .btn-add { font-size: 20px; background-color: #28a745; color: white; width: 150px; height: 60px; margin-right: 15px;}
        .btn-update { font-size: 20px; background-color: #007bff; color: white; width: 150px; height: 70px;}
        .btn-delete { font-size: 20px; background-color: #dc3545; color: white; width: 150px; height: 50px;}

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

div > .btn {
    display: inline-block;
    vertical-align: top;  
}

.update-action {
    position: relative;
    top: 0px;
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
<?php include "../includes/header.php";?>
    <div class="container-page">
    <h1>Patient</h1>

<?php if (isset($message)) echo "<div class='message'>$message</div>"; ?>

    <div class="action-bar">
        <div class="search-bar">
            <form method="GET">
                <input type="text" name="search" placeholder="Search patient...">
                <button class="btn btn-update" type="submit" style="height: 60px;">Search</button>
            </form>
        </div>

        <div>
            <button class="btn btn-add" onclick="showAddForm()">Add Patient</button>
        </div>
    </div>

    <table>
        <tr>
            <th>ID</th>
            <th>First Name</th>
            <th>Middle Initial</th>
            <th>Last Name</th>
            <th>Date of Birth</th>
            <th>Gender</th>
            <th>Contact</th>
            <th>Email</th>
            <th>Address</th>
            <th>Actions</th>
        </tr>

       <?php if (!empty($patientList)): ?>
            <?php foreach ($patientList as $patient): ?>
                <tr>
                    <td><?= htmlspecialchars($patient['PAT_ID']) ?></td>
                    <td><?= htmlspecialchars($patient['PAT_FIRST_NAME']) ?></td>
                    <td><?= htmlspecialchars($patient['PAT_MIDDLE_INIT']) ?></td>
                    <td><?= htmlspecialchars($patient['PAT_LAST_NAME']) ?></td>
                    <td><?= htmlspecialchars($patient['PAT_DOB']) ?></td>
                    <td><?= htmlspecialchars($patient['PAT_GENDER']) ?></td>
                    <td><?= htmlspecialchars($patient['PAT_CONTACT_NUM']) ?></td>
                    <td><?= htmlspecialchars($patient['PAT_EMAIL']) ?></td>
                    <td><?= htmlspecialchars($patient['PAT_ADDRESS']) ?></td>
                    <td>
                        <button type="button" style="height: 40px; width: 100px; margin-right: 10px;"class="btn btn-update"
                            onclick="showUpdateForm(
                                '<?= $patient['PAT_ID'] ?>',
                                '<?= htmlspecialchars($patient['PAT_FIRST_NAME'], ENT_QUOTES) ?>',
                                '<?= htmlspecialchars($patient['PAT_MIDDLE_INIT'], ENT_QUOTES) ?>',
                                '<?= htmlspecialchars($patient['PAT_LAST_NAME'], ENT_QUOTES) ?>',
                                '<?= htmlspecialchars($patient['PAT_DOB'], ENT_QUOTES) ?>',
                                '<?= htmlspecialchars($patient['PAT_GENDER'], ENT_QUOTES) ?>',
                                '<?= htmlspecialchars($patient['PAT_CONTACT_NUM'], ENT_QUOTES) ?>',
                                '<?= htmlspecialchars($patient['PAT_EMAIL'], ENT_QUOTES) ?>',
                                '<?= htmlspecialchars($patient['PAT_ADDRESS'], ENT_QUOTES) ?>',
                            )">Edit</button>
                        <?php if ($currentRole === 'superadmin'): ?>
<form method="POST" style="display:inline;" onsubmit="return confirm('Are you sure you want to delete this patient?');">
    <input type="hidden" name="delete_patient" value="<?= $patient['PAT_ID'] ?>">
    <button type="submit" class="btn btn-delete" style="width: 100px; height: 40px;">Delete</button>
</form>
<?php else: ?>
<form method="POST" style="display:inline;" onsubmit="alert('You are not allowed to delete a patient!'); return false;">
    <input type="hidden" name="delete_patient" value="<?= $patient['PAT_ID'] ?>">
    <button type="submit" class="btn btn-delete" style="width: 100px; height: 40px;">Delete</button>
</form>
<?php endif; ?>
                    </td>
                </tr>
            <?php endforeach; ?>
        <?php else: ?>
            <tr><td colspan="10">No patient found.</td></tr>
        <?php endif; ?>
    </table>

    <div id="addForm" class="add-form-container">
        <form method="POST" action="<?= htmlspecialchars($_SERVER['PHP_SELF']) ?>">
            <h2>Add New Patient</h2>


            <label>First Name:</label>
            <input type="text" name="pat_first_name" required>

            <label>Middle Initial:</label>
            <input type="text" name="pat_middle_init" maxlength="1">

            <label>Last Name:</label>
            <input type="text" name="pat_last_name" required>

            <label>Date of Birth:</label>
            <input type="date" name="pat_dob" required>

            <label>Gender:</label>
            <input type="text" name="pat_gender" required>

            <label>Contact Number:</label>
            <input type="text" name="pat_contact_num" required>

            <label>Email:</label>
            <input type="email" name="pat_email" required>

            <label>Address:</label>
            <input type="text" name="pat_address" required>

            <div class="form-actions">
                <button type="submit" name="add_patient" class="btn btn-add">Save</button>
                <button type="button" class="btn btn-delete" onclick="hideAddForm()" style="height: 60px;">Cancel</button>
            </div>
        </form>
    </div>

    <div id="updateForm" class="add-form-container">
        <form method="POST" action="">
            <h2>Update Patient</h2>
            <input type="hidden" name="pat_id" id="update_id">

            <label>First Name:</label><input type="text" name="pat_first_name" id="update_first" required>
            <label>Middle Initial:</label><input type="text" name="pat_middle_init" id="update_mid" maxlength="1">
            <label>Last Name:</label><input type="text" name="pat_last_name" id="update_last" required>
            <label>Date of Birth:</label><input type="date" name="pat_dob" id="update_dob" required>
            <label>Gender:</label><input type="text" name="pat_gender" id="update_gender" required>
            <label>Contact Number:</label><input type="text" name="pat_contact_num" id="update_contact" required>
            <label>Email:</label><input type="email" name="pat_email" id="update_email" required>
            <label>Address:</label><input type="text" name="pat_address" id="update_address" required>

            <div class="form-actions">
                <button type="submit" name="update_patient" class="btn btn-add">Update</button>
                <button type="button" class="btn btn-delete" onclick="hideUpdateForm()" style="height: 60px;">Cancel</button>
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
    setTimeout(() => addForm.classList.add('show'), 10); // for animation
}

function hideAddForm() {
    const overlay = document.getElementById('overlay');
    const addForm = document.getElementById('addForm');

    addForm.classList.remove('show');
    overlay.classList.remove('show');
    setTimeout(() => addForm.style.display = 'none', 400); // match CSS transition
}

function showUpdateForm(id, first, mid, last, dob, gender, contact, email, address) {
    const overlay = document.getElementById('overlay');
    const updateForm = document.getElementById('updateForm');

    overlay.classList.add('show');
    updateForm.style.display = 'block';
    setTimeout(() => updateForm.classList.add('show'), 10);

    document.getElementById('update_id').value = id;
    document.getElementById('update_first').value = first;
    document.getElementById('update_mid').value = mid;
    document.getElementById('update_last').value = last;
    document.getElementById('update_dob').value = dob;
    document.getElementById('update_gender').value = gender;
    document.getElementById('update_contact').value = contact;
    document.getElementById('update_email').value = email;
    document.getElementById('update_address').value = address;
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
