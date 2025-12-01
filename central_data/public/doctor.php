<?php
session_start();

require_once __DIR__ . "/../../central_data/config/Database.php";
require_once __DIR__ . "/../../central_data/classes/Doctor.php";

$db = new Database();
$conn = $db->getConn();

if (!isset($_SESSION['role'])) {
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
        font-size: 80px;
    '>
        <h1>Access Denied</h1>
        <p>You must be logged in to access this page.</p>
    </div>";
    exit;
}

$currentRole = ucfirst(strtolower($_SESSION['role']));

if (!in_array($currentRole, ['Superadmin', 'Doctor'])) {
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
        <p>Only SuperAdmin or Doctor can access this page.</p>
    </div>
    ";
    exit;
}

$currentRole = $_SESSION['role'];
$doctorObj = new Doctor();
$doctorList = $doctorObj->get_all($currentRole);

if ($currentRole === "Doctor") {

    $docID = $_SESSION['doc_id'];

    $prevAppt   = $doctorObj->getPreviousAppointments($currentRole, $docID);
    $todayAppt  = $doctorObj->getTodaysAppointments($currentRole, $docID);
    $futureAppt = $doctorObj->getFutureAppointments($currentRole, $docID, $docID);
}

if (isset($_POST['add_doctor'])) {
    $spec_id = $_POST['spec_id'];
    $check = $conn->prepare("SELECT SPEC_ID FROM SPECIALIZATION WHERE SPEC_ID = ?");
    $check->bind_param("i", $spec_id);
    $check->execute();
    $check->store_result();

    if ($check->num_rows === 0) {
        echo "<script>
                alert('Error: The specialization you selected does not exist.');
                window.history.back();
              </script>";
        exit;
    }
    $check->close();
    $data = [
        'doc_first_name'  => $_POST['doc_first_name'],
        'doc_last_name'   => $_POST['doc_last_name'],
        'doc_middle_init' => $_POST['doc_middle_init'],
        'doc_contact_num' => $_POST['doc_contact_num'],
        'doc_email'       => $_POST['doc_email'],
        'spec_id'         => $spec_id
    ];

    $result = $doctorObj->add_doctor($currentRole, $data);

    if ($result['success']) {
        header("Location: doctor.php?added=1");
        exit;
    }
}

if (isset($_POST['update_doctor'])) {
    $spec_id = $_POST['spec_id'];
    $check = $conn->prepare("SELECT SPEC_ID FROM SPECIALIZATION WHERE SPEC_ID = ?");
    $check->bind_param("i", $spec_id);
    $check->execute();
    $check->store_result();

    if ($check->num_rows === 0) {
        echo "<script>
                alert('Error: The specialization you selected does not exist.');
                window.history.back();
              </script>";
        exit;
    }

    $id = $_POST['DOC_ID'];

    $data = [
        'doc_first_name'  => $_POST['doc_first_name'],
        'doc_last_name'   => $_POST['doc_last_name'],
        'doc_middle_init' => $_POST['doc_middle_init'],
        'doc_contact_num' => $_POST['doc_contact_num'],
        'doc_email'       => $_POST['doc_email'],
        'spec_id'         => $_POST['spec_id']
    ];

    $result = $doctorObj->update_doctor($currentRole, $id, $data);

    if($result['success']) {
        header("Location: doctor.php?updated=1");
        exit;
    } else {
        die("Error adding doctor: " . $result['message']);
    }
}

if (strtolower($currentRole) === 'superadmin' && isset($_POST['delete_doctor'])) {
    $id = $_POST['DOC_ID'];
    $doctorObj->delete_doctor($currentRole, $id);
    header("Location: doctor.php?deleted=1");
    exit;
}

if (isset($_GET['added']))   $message = "Doctor added successfully!";
if (isset($_GET['updated'])) $message = "Doctor updated successfully!";
if (isset($_GET['deleted'])) $message = "Doctor deleted successfully!";

if (isset($_GET['search']) && !empty(trim($_GET['search']))) {
    $keyword = trim($_GET['search']);
    $doctorList = $doctorObj->search_doctor($currentRole, $keyword);
}

?>

<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Doctor Dashboard - Urban Medical Hospital</title>

<style>
body{
    margin:0;
    font-family:'Port Lligat Slab',serif;
    background:#FFF9F9;
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
.container-page{ padding:70px; }
.container-page h1{ font-size:80px; margin-top:120px; color:black; }
.action-bar{ display:flex; justify-content:space-between; margin:20px 0; }
.btn{
    padding:10px 14px;
    border:none;
    border-radius:5px;
    cursor:pointer;
    font-weight:bold;
}
.btn-add{ background:#28a745; color:white; }
.btn-update{ background:#007bff; color:white; }
.btn-delete{ background:#dc3545; color:white; }
table{ width:100%; border-collapse:collapse; background:white; margin-top:25px; }
th,td{ padding:12px; border-bottom:1px solid #ddd; text-align:center; font-size:20px; }
th{ background:#f1f1f1; }
.message{ color:green; font-weight:bold; }
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
.form-actions{ display:flex; justify-content:space-between; margin-top:20px; }
.tabs{ display:flex; gap:20px; margin-top:40px; }
.tab-btn{
    padding:10px 20px;
    background:#007bff;
    color:white; border-radius:5px;
    cursor:pointer;
}
.tab-btn.active{ background:#0056b3; }
.tab-content{ display:none; margin-top:20px; }
.tab-content.active{ display:block; }

footer {
        width: 100%;
        background-color: #0041c7;
        color: white;
        padding: 30px 50px;
        display: flex;
        justify-content: center;
        align-items: center;
        margin-top: 201px;
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

<h1 style="margin-top: 120px;">Doctor</h1>

<?php if(isset($message)) echo "<p class='message'>$message</p>"; ?>

<?php if($currentRole === "Superadmin"): ?>

<div class="action-bar">
        <div class="search-bar">
            <form method="GET">
                <input type="text" name="search" placeholder="Search doctor...">
                <button class="btn btn-update" type="submit" style="height: 60px; font-size: 20px;">Search</button>
            </form>
        </div>

        <div>
            <button class="btn btn-add" onclick="showAddForm()" style="margin-top: 30px; width: 150px; height: 50px; font-size: 20px;">Add Doctor</button>
        </div>
    </div>

<table>
<tr>
    <th>ID</th>
    <th>First Name</th>
    <th>Middle Name</th>
    <th>Last Name</th>
    <th>Contact Number</th>
    <th>Email</th>
    <th>Specialization</th>
    <th>Actions</th>
</tr>

<?php if(!empty($doctorList)): foreach($doctorList as $doc): ?>
<tr>
<td><?= $doc['DOC_ID'] ?></td>
<td><?= $doc['DOC_FIRST_NAME'] ?></td>
<td><?= $doc['DOC_MIDDLE_INIT'] ?></td>
<td><?= $doc['DOC_LAST_NAME'] ?></td>
<td><?= $doc['DOC_CONTACT_NUM'] ?></td>
<td><?= $doc['DOC_EMAIL'] ?></td>
<td><?= $doc['SPEC_NAME'] ?></td>

<td>
<button class="btn btn-update"
    onclick="showUpdateForm(
        '<?= $doc['DOC_ID'] ?>',
        '<?= addslashes($doc['DOC_FIRST_NAME']) ?>',
        '<?= addslashes($doc['DOC_MIDDLE_INIT']) ?>',
        '<?= addslashes($doc['DOC_LAST_NAME']) ?>',
        '<?= addslashes($doc['DOC_CONTACT_NUM']) ?>',
        '<?= addslashes($doc['DOC_EMAIL']) ?>',
        '<?= addslashes($doc['SPEC_ID']) ?>'
    )" style="width: 100px; height: 40px; font-size: 18px;">Edit</button>

<form method='POST' style='display:inline;' onsubmit="return confirm('Are you sure?')">
    <input type='hidden' name='DOC_ID' value='<?= $doc['DOC_ID'] ?>'>
    <button class='btn btn-delete' name='delete_doctor' style="width: 100px; height: 40px; font-size: 18px;">Delete</button>
</form>
</td>

</tr>
<?php endforeach; else: ?>
<tr><td colspan="8">No doctors found.</td></tr>
<?php endif; ?>
</table>

<div id="overlay" class="overlay"></div>

<div id="addForm" class="add-form-container">
    <form method="POST">
        <h2>Add New Doctor</h2>

        <label>First Name:</label>
        <input type="text" name="doc_first_name" required>

        <label>Middle Initial:</label>
        <input type="text" name="doc_middle_init" maxlength="1">

        <label>Last Name:</label>
        <input type="text" name="doc_last_name" required>

        <label>Contact Number:</label>
        <input type="text" name="doc_contact_num" required>

        <label>Email:</label>
        <input type="text" name="doc_email" required>

        <label>Specialization ID:</label>
        <input type="text" name="spec_id" required>

        <div class="form-actions">
            <button class="btn btn-add" name="add_doctor" style="font-size: 20px; width: 160px; height: 60px;">Save</button>
            <button type="button" class="btn btn-delete" onclick="hideAddForm()" style="font-size: 20px; width: 160px; height: 60px;">Cancel</button>
        </div>
    </form>
</div>

<div id="updateForm" class="add-form-container">
    <form method="POST">
        <h2>Update Doctor</h2>

        <input type="hidden" id="update_id" name="DOC_ID">

        <label>First Name:</label>
        <input type="text" id="update_first" name="doc_first_name" required>

        <label>Middle Initial:</label>
        <input type="text" id="update_mid" name="doc_middle_init">

        <label>Last Name:</label>
        <input type="text" id="update_last" name="doc_last_name" required>

        <label>Contact Number:</label>
        <input type="text" id="update_contact" name="doc_contact_num" required>

        <label>Email:</label>
        <input type="text" id="update_email" name="doc_email" required>

        <label>Specialization ID:</label>
        <input type="text" id="update_spec" name="spec_id" required>

        <div class="form-actions">
            <button class="btn btn-add" name="update_doctor" style="height: 60px; width: 150px; font-size: 20px;">Update</button>
            <button type="button" class="btn btn-delete" onclick="hideUpdateForm()" style="height: 60px; width: 150px; font-size: 20px;">Cancel</button>
        </div>
    </form>
</div>


<?php endif; ?>

<?php if($currentRole === "Doctor"): ?>

<div class="tabs" style="margin-top: 20px;">
    <button class="tab-btn active" onclick="showTab('prev')" style="height: 60px; width: 110px; font-size: 20px; font-family: 'Port Lligat Slab', serif;">Previous</button>
    <button class="tab-btn" onclick="showTab('today')" style="height: 60px; width: 110px; font-size: 20px; font-family: 'Port Lligat Slab', serif;">Today</button>
    <button class="tab-btn" onclick="showTab('future')" style="height: 60px; width: 110px; font-size: 20px; font-family: 'Port Lligat Slab', serif;">Future</button>
</div>

<div id="prev" class="tab-content active">
<h2 style="font-size: 40px;">Previous Appointments</h2>

<?php if(count($prevAppt) > 0): ?>
<table>
<tr><th>Date</th><th>Time</th><th>Patient</th><th>Service</th></tr>

<?php foreach($prevAppt as $row): ?>
<tr>
<td><?= $row['APPT_DATE'] ?></td>
<td><?= $row['APPT_TIME'] ?></td>
<td><?= $row['PAT_FIRST_NAME']." ".$row['PAT_LAST_NAME'] ?></td>
<td><?= $row['SERV_NAME'] ?></td>
</tr>
<?php endforeach; ?>

</table>
<?php else: ?>
<p style="font-size: 25px; margin-top: 10px;">No previous appointments.</p>
<?php endif; ?>
</div>

<div id="today" class="tab-content">
<h2 style="font-size: 40px;">Today's Appointments</h2>

<?php if(count($todayAppt) > 0): ?>
<table>
<tr><th>Time</th><th>Patient</th><th>Service</th></tr>

<?php foreach($todayAppt as $row): ?>
<tr>
<td><?= $row['APPT_TIME'] ?></td>
<td><?= $row['PAT_FIRST_NAME']." ".$row['PAT_LAST_NAME'] ?></td>
<td><?= $row['SERV_NAME'] ?></td>
</tr>
<?php endforeach; ?>

</table>
<?php else: ?>
<p style="font-size: 25px; margin-top: 10px;">No appointments today.</p>
<?php endif; ?>
</div>

<div id="future" class="tab-content">
<h2 style="font-size: 40px;">Future Appointments</h2>

<?php if(count($futureAppt) > 0): ?>
<table>
<tr><th>Date</th><th>Time</th><th>Patient</th><th>Service</th></tr>

<?php foreach($futureAppt as $row): ?>
<tr>
<td><?= $row['APPT_DATE'] ?></td>
<td><?= $row['APPT_TIME'] ?></td>
<td><?= $row['PAT_FIRST_NAME']." ".$row['PAT_LAST_NAME'] ?></td>
<td><?= $row['SERV_NAME'] ?></td>
</tr>
<?php endforeach; ?>

</table>
<?php else: ?>
<p style="font-size: 25px; margin-top: 10px;">No future appointments.</p>
<?php endif; ?>
</div>

<?php endif; ?>

</div>

<footer style="margin-top: 199px;">
<h2>&copy; 2025 Urban Medical Hospital by Hollys Group. All Rights Reserved.</h2>
</footer>

<script>
function showAddForm(){
    const form = document.getElementById('addForm');
    const overlay = document.getElementById('overlay');
    form.classList.add('show');
    overlay.classList.add('show');
}

function hideAddForm(){
    const form = document.getElementById('addForm');
    const overlay = document.getElementById('overlay');

    form.classList.remove('show');
    overlay.classList.remove('show');
}

function showUpdateForm(id, first, mid, last, contact, email, spec) {
    const form = document.getElementById('updateForm');
    const overlay = document.getElementById('overlay');

    update_id.value = id;
    update_first.value = first;
    update_mid.value = mid;
    update_last.value = last;
    update_contact.value = contact;
    update_email.value = email;
    update_spec.value = spec;

    form.classList.add('show');
    overlay.classList.add('show');
}

function hideUpdateForm() {
    const form = document.getElementById('updateForm');
    const overlay = document.getElementById('overlay');

    form.classList.remove('show');
    overlay.classList.remove('show');
}


function showTab(tab){
    document.querySelectorAll('.tab-btn').forEach(b=>b.classList.remove('active'));
    document.querySelectorAll('.tab-content').forEach(c=>c.classList.remove('active'));
    event.target.classList.add('active');
    document.getElementById(tab).classList.add('active');
}
</script>

</body>
</html>
