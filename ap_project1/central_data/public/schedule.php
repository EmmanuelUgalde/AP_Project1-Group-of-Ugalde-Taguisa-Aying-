<?php
session_start();
require_once __DIR__ . "/../../central_data/classes/Schedule.php";

if (!isset($_SESSION['role'])) {
    echo "<div style='text-align:center; margin-top:200px; font-family: Port Lligat Slab, serif;'>
            <h1 style='color:red;'>Access Denied</h1>
            <p>You must be logged in to access this page.</p>
          </div>";
    exit;
}

$currentRole = ucfirst(strtolower($_SESSION['role']));

if (!in_array($currentRole, ['Superadmin', 'Doctor'])) {
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
        <p style='color: black;'>Only SuperAdmin or Doctor can access this page.</p>
    </div>";
    exit;
}

$db = new Database();
$conn = $db->getConn();
$scheduleObj = new Schedule($conn);

if (isset($_POST['add_schedule']) && in_array(strtolower($currentRole), ['superadmin', 'doctor'])) {

    $doc_id = $_POST['doc_id'];

    $check = $conn->prepare("SELECT DOC_ID FROM DOCTOR WHERE DOC_ID = ?");
    $check->bind_param("i", $doc_id);
    $check->execute();
    $check->store_result();

    if ($check->num_rows === 0) {
        echo "<script>
                alert('Error: The doctor you selected does not exist.');
                window.history.back();
              </script>";
        exit;
    }

    $data = [
        'sched_days'  => $_POST['sched_days'],
        'sched_start' => $_POST['sched_start'],
        'sched_end'   => $_POST['sched_end'],
        'doc_id'      => $doc_id
    ];

    $result = $scheduleObj->add_sched(strtolower($currentRole), $data);

    if ($result['success']) {
        header('Location: schedule.php?added=1');
        exit;
    } else {
        die('Error adding schedule: ' . $result['message']);
    }
}

if (isset($_POST['update_schedule']) && in_array(strtolower($currentRole), ['superadmin', 'doctor'])) {

    $doc_id = $_POST['doc_id'];

    $check = $conn->prepare("SELECT DOC_ID FROM DOCTOR WHERE DOC_ID = ?");
    $check->bind_param("i", $doc_id);
    $check->execute();
    $check->store_result();

    if ($check->num_rows === 0) {
        echo "<script>
                alert('Error: The doctor you selected does not exist.');
                window.history.back();
              </script>";
        exit;
    }

    $id = $_POST['sched_id'];

    $data = [
        'sched_days'  => $_POST['sched_days'],
        'sched_start' => $_POST['sched_start'],
        'sched_end'   => $_POST['sched_end'],
        'doc_id'      => $doc_id
    ];

    $result = $scheduleObj->update_sched(strtolower($currentRole), $id, $data);

    if ($result['success']) {
        header("Location: schedule.php?updated=1");
        exit;
    } else {
        die("Error updating schedule: " . $result['message']);
    }
}

if (isset($_POST['delete_schedule']) && in_array(strtolower($currentRole), ['superadmin', 'doctor'])) {
    $id = $_POST['delete_schedule'];
    $result = $scheduleObj->delete_sched($currentRole, $id);
    if ($result['success']) {
        header("Location: schedule.php?deleted=1");
        exit;
    } else {
        die("Error deleting schedule: " . $result['message']);
    }
}

$search = $_GET['search'] ?? '';
$today = date('l');
if ($search) {
    $like = "%$search%";
    if ($currentRole === 'Superadmin') {
        $stmt = $conn->prepare("SELECT S.*, D.DOC_FIRST_NAME, D.DOC_MIDDLE_INIT, D.DOC_LAST_NAME FROM SCHEDULE S LEFT JOIN DOCTOR D ON S.DOC_ID=D.DOC_ID WHERE S.SCHED_DAYS LIKE ? OR D.DOC_FIRST_NAME LIKE ? OR D.DOC_LAST_NAME LIKE ? ORDER BY S.SCHED_ID ASC");
        $stmt->bind_param("sss", $like, $like, $like);
    } else {
        $docId = intval($_SESSION['doc_id']);
        $stmt = $conn->prepare("SELECT S.*, D.DOC_FIRST_NAME, D.DOC_MIDDLE_INIT, D.DOC_LAST_NAME FROM SCHEDULE S LEFT JOIN DOCTOR D ON S.DOC_ID=D.DOC_ID WHERE S.DOC_ID=? AND (S.SCHED_DAYS LIKE ? OR D.DOC_FIRST_NAME LIKE ? OR D.DOC_LAST_NAME LIKE ?) ORDER BY S.SCHED_ID ASC");
        $stmt->bind_param("isss", $docId, $like, $like, $like);
    }
} else {
    if ($currentRole === 'Superadmin') {
        $stmt = $conn->prepare("SELECT S.*, D.DOC_FIRST_NAME, D.DOC_MIDDLE_INIT, D.DOC_LAST_NAME FROM SCHEDULE S LEFT JOIN DOCTOR D ON S.DOC_ID=D.DOC_ID ORDER BY S.SCHED_ID ASC");
    } else {
        $docId = intval($_SESSION['doc_id']);
        $stmt = $conn->prepare("SELECT S.*, D.DOC_FIRST_NAME, D.DOC_MIDDLE_INIT, D.DOC_LAST_NAME FROM SCHEDULE S LEFT JOIN DOCTOR D ON S.DOC_ID=D.DOC_ID WHERE S.DOC_ID=? ORDER BY S.SCHED_ID ASC");
        $stmt->bind_param("i", $docId);
    }
}
$stmt->execute();
$schedules = $stmt->get_result();
?>

<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Schedule - Urban Medical Hospital</title>
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
    <h1>Schedule</h1>
    <?php if (isset($_GET['added'])) echo "<div class='message'>Specialization added successfully!</div>"; ?>
    <?php if (isset($_GET['updated'])) echo "<div class='message'>Specialization updated successfully!</div>"; ?>
    <?php if (isset($_GET['deleted'])) echo "<div class='message'>Specialization deleted successfully!</div>"; ?>

    <div class="action-bar">
        <div class="search-bar">
            <form method="GET">
                <input type="text" name="search" placeholder="Search schedule or doctor..." value="<?= htmlspecialchars($search) ?>">
                <button class="btn btn-update" type="submit">Search</button>
            </form>
        </div>
        <?php if ($currentRole === 'Superadmin' || $currentRole === 'Doctor'): ?>
            <button class="btn btn-add" onclick="showAddForm()" style="width: 160px;">Add Schedule</button>
        <?php endif; ?>
    </div>

    <table>
        <tr><th>ID</th><th>Days</th><th>Start</th><th>End</th><th>Doctor</th><th>Actions</th></tr>
        <?php if($schedules && $schedules->num_rows): while($r=$schedules->fetch_assoc()): ?>
            <tr>
                <td><?= htmlspecialchars($r['SCHED_ID']) ?></td>
                <td><?= htmlspecialchars($r['SCHED_DAYS']) ?></td>
                <td><?= date("h:i A", strtotime($r['SCHED_START_TIME'])) ?></td>
                <td><?= date("h:i A", strtotime($r['SCHED_END_TIME'])) ?></td>
                <td><?= htmlspecialchars($r['DOC_FIRST_NAME'].' '.$r['DOC_MIDDLE_INIT'].' '.$r['DOC_LAST_NAME']) ?></td>
                <td>
                    <?php
                    $canEdit = ($currentRole==='Superadmin') || ($currentRole==='Doctor' && intval($_SESSION['doc_id']) === intval($r['DOC_ID']));
                    if($canEdit): ?>
                        <button class="btn btn-update" onclick="showUpdateForm('<?= $r['SCHED_ID'] ?>',
                        '<?= htmlspecialchars($r['SCHED_DAYS'],ENT_QUOTES) ?>',
                        '<?= htmlspecialchars($r['SCHED_START_TIME'],ENT_QUOTES) ?>',
                        '<?= htmlspecialchars($r['SCHED_END_TIME'],ENT_QUOTES) ?>',
                        '<?= intval($r['DOC_ID']) ?>')" style="width: 100px; height: 40px;">Edit</button>
                    <?php endif;
                    if($currentRole==='Superadmin'): ?>
                        <form method="POST" style="display:inline;" onsubmit="return confirm('Delete this schedule?');">
                            <input type="hidden" name="delete_schedule" value="<?= $r['SCHED_ID'] ?>">
                            <button class="btn btn-delete" type="submit" style="width: 100px; height: 40px;">Delete</button>
                        </form>
                    <?php endif; ?>
                </td>
            </tr>
        <?php endwhile; else: ?>
            <tr><td colspan="6">No schedules found.</td></tr>
        <?php endif; ?>
    </table>
</div>

<div id="addForm" class="add-form-container">
  <form method="POST" action="">
    <h2>Add Schedule</h2>
    <label>Days (e.g. Monday,Tuesday):</label>
    <input type="text" name="sched_days" required>
    <label>Start Time:</label>
    <input type="time" name="sched_start" required>
    <label>End Time:</label>
    <input type="time" name="sched_end" required>
    <label>Doctor ID:</label>
    <input type="number" name="doc_id" required>
    <div class="form-actions">
      <button name="add_schedule" class="btn btn-add" type="submit" style="margin-top: 15px;">Save</button>
      <button type="button" class="btn btn-delete" onclick="hideAddForm()" style="height: 60px; width: 160px; align-items: center; margin-top: 15px;">Cancel</button>
    </div>
  </form>
</div>

<div id="updateForm" class="add-form-container">
  <form method="POST" action="">
    <h2>Update Schedule</h2>
    <input type="hidden" name="sched_id" id="update_id">
    <label>Days (e.g. Monday,Tuesday):</label>
    <input type="text" name="sched_days" id="update_days" required>
    <label>Start Time:</label>
    <input type="time" name="sched_start" id="update_start" required>
    <label>End Time:</label>
    <input type="time" name="sched_end" id="update_end" required>
    <label>Doctor ID:</label>
    <input type="number" name="doc_id" id="update_doc" required>
    <div class="form-actions">
      <button name="update_schedule" class="btn btn-add" type="submit">Update</button>
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
